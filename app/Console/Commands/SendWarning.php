<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\QontakServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendWarning extends Command
{
  protected $signature = 'app:send-warning';
  protected $description = 'Kirim pesan warning untuk tagihan bulan ini (kirim tanggal 2-3, 900/hari, reset akhir bulan)';

  public function handle()
  {
    $today = Carbon::today('Asia/Jakarta');
    $this->info("📅 Tanggal hari ini: {$today->format('Y-m-d')}");

    // ============================================
    // BAGIAN 1: RESET PADA AKHIR BULAN
    // ============================================
    $isLastDayOfMonth = $today->isLastOfMonth();

    if ($isLastDayOfMonth) {
      $this->info("🔄 Hari ini akhir bulan ({$today->format('Y-m-d')}) - Melakukan reset warning_sent");

      $resetCount = Customer::where('warning_sent', 1)->update(['warning_sent' => null]);

      Log::info("🔄 Reset warning_sent pada akhir bulan", [
        'tanggal' => $today->format('Y-m-d'),
        'bulan' => $today->format('F Y'),
        'total_direset' => $resetCount
      ]);

      $this->info("✅ Reset selesai: {$resetCount} customer direset");
      return Command::SUCCESS;
    }

    // ============================================
    // BAGIAN 2: KIRIM PESAN PADA TANGGAL 2 & 3
    // ============================================
    if (!in_array($today->day, [2, 3, 4, 5, 6])) {
      $this->info("⏭️ Command hanya berjalan pada tanggal 2-3 (kirim) dan akhir bulan (reset). Hari ini tanggal {$today->day}.");
      Log::info("SendWarning skipped - hanya berjalan tanggal 2-3 & akhir bulan", ['tanggal' => $today->format('Y-m-d')]);
      return Command::SUCCESS;
    }

    $bulanIni = $today->copy();
    $awalBulanIni = $bulanIni->copy()->startOfMonth();
    $akhirBulanIni = $bulanIni->copy()->endOfMonth();

    $this->info("🔍 Mencari tagihan bulan ini: {$bulanIni->format('F Y')}");
    $this->info("📅 Periode: {$awalBulanIni->format('d M Y')} - {$akhirBulanIni->format('d M Y')}");
    $this->info("💡 Info: Reset warning_sent akan dilakukan pada akhir bulan");

    $chat = new QontakServices();

    // Get unpaid invoices dari bulan ini
    $invoices = Invoice::with(['customer']) // Eager loading untuk optimasi
      ->where('status_id', 7) // Belum bayar
      ->whereMonth('jatuh_tempo', Carbon::now()->month)
      ->whereYear('jatuh_tempo', Carbon::now()->year)
      ->where('paket_id', '!=', 11) // Bukan paket khusus
      ->whereHas('customer', function ($query) {
        $query->whereNull('deleted_at')
          ->whereIn('status_id', [3, 9]) // Hanya customer aktif
          ->whereNull('warning_sent'); // HANYA yang belum dikirim
      })
      ->orderBy('customer_id')
      ->orderBy('jatuh_tempo', 'desc')
      ->get();

    // Group by customer dan ambil invoice terbaru per customer
    $customerInvoices = [];
    foreach ($invoices as $invoice) {
      $customerId = $invoice->customer_id;
      if (!isset($customerInvoices[$customerId])) {
        $customerInvoices[$customerId] = $invoice;
      }
    }

    $totalFound = count($customerInvoices);
    $this->info("📊 Total customer dengan tagihan bulan ini yang belum dibayar & belum dikirim: {$totalFound}");

    if ($totalFound === 0) {
      $this->info("✅ Tidak ada tagihan bulan ini yang perlu dikirim warning.");
      return Command::SUCCESS;
    }

    // Batching: 900 customer per hari
    $limit = 900;
    $customerInvoices = array_slice($customerInvoices, 0, $limit, true);

    $this->info("📊 Mengirim ke maksimal {$limit} customer (Tanggal {$today->day})");

    $softDeletedCount = 0;
    $sentCount = 0;
    $failedCount = 0;
    $invalidPhoneCount = 0;
    $alreadySentDuringLoopCount = 0; // Track jika ada yang sudah dikirim saat loop

    // Progress bar
    $bar = $this->output->createProgressBar(count($customerInvoices));
    $bar->start();

    foreach ($customerInvoices as $invoice) {
      $customer = $invoice->customer;

      if (!$customer) {
        $this->warn("\n⚠️ Customer tidak ditemukan untuk invoice {$invoice->id}");
        $bar->advance();
        continue;
      }

      $customerName = $customer->nama_customer;

      // Skip jika customer sudah soft deleted
      if ($customer->trashed()) {
        Log::info("⏭️ Skip customer soft deleted: " . $customerName, [
          'customer_id' => $customer->id,
          'invoice_id' => $invoice->id
        ]);
        $softDeletedCount++;
        $bar->advance();
        continue;
      }

      // PROTEKSI LAYER 1: Double-check warning_sent (refresh dari database)
      // Ini mencegah race condition jika command dijalankan bersamaan
      $customer->refresh();
      if ($customer->warning_sent == 1) {
        Log::info("⏭️ Skip - customer sudah dikirim warning (detected during loop)", [
          'customer' => $customerName,
          'customer_id' => $customer->id,
          'invoice_id' => $invoice->id,
          'reason' => 'warning_sent = 1 saat double-check'
        ]);
        $alreadySentDuringLoopCount++;
        $bar->advance();
        continue;
      }

      // Validasi nomor HP
      if (empty($customer->no_hp) || strlen($customer->no_hp) < 10) {
        Log::warning("⚠️ Nomor HP tidak valid", [
          'customer' => $customerName,
          'customer_id' => $customer->id,
          'no_hp' => $customer->no_hp
        ]);
        $invalidPhoneCount++;
        $bar->advance();
        continue;
      }

      // Kirim warning
      try {
        $hasil = $chat->notifTagihan($customer->no_hp, $invoice);

        // PENANGANAN: Jika Qontak API menolak / memuntahkan Error di level payload (meski HTTP 200 OK)
        if (isset($hasil['status']) && ($hasil['status'] === 'failed' || $hasil['status'] === 'error')) {
          $errorMessage = $hasil['error']['message'] ?? $hasil['message'] ?? 'Message undeliverable / rejected';
          $errorDetail = $hasil['error']['details'] ?? '';

          Log::warning("Gagal kirim warning via Qontak", [
            'customer' => $customerName,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'error' => $errorMessage,
            'detail' => $errorDetail
          ]);
          $failedCount++;

          // OPTIMASI LOG KETIKA GAGAL: Jangan update `warning_sent` customer, agar bisa di-retry esok hari.
          DB::table('whats_log')->insert([
            'customer_id' => $customer->id,
            'jenis_pesan' => 'warning_bayar',
            'pesan' => 'Tagihan Periode ' . $bulanIni->format('F Y') . ' (Ditolak Qontak)',
            'qontak_broadcast_id' => $hasil['message_id'] ?? null,
            'status_pengiriman' => 'failed',
            'no_tujuan' => $customer->no_hp,
            'error_message' => trim($errorMessage . ' ' . $errorDetail),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

          // PENANGANAN: Gagal murni dari fungsi Local (CURL/Internal Server Error)
        } elseif (isset($hasil['success']) && $hasil['success'] === false) {
          Log::error("Error internal saat merangkai Payload Warning", [
            'customer' => $customerName,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'error' => $hasil['message'] ?? $hasil['error'] ?? 'Internal Server Error'
          ]);
          $failedCount++;

          DB::table('whats_log')->insert([
            'customer_id' => $customer->id,
            'jenis_pesan' => 'warning_bayar',
            'pesan' => 'Tagihan Periode ' . $bulanIni->format('F Y') . ' (Sistem Error)',
            'qontak_broadcast_id' => null,
            'status_pengiriman' => 'failed',
            'no_tujuan' => $customer->no_hp,
            'error_message' => (is_string($hasil['error'] ?? null)) ? $hasil['error'] : ($hasil['message'] ?? 'Internal Request Failed'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

          // PENANGANAN: Sukses Meluncur ke Qontak Antrian (Status = 'pending' atau 'todo')
        } else {
          $qontakStatus = $hasil['status'] ?? 'pending'; // Normalnya Qontak membalas "todo" atau "pending"
          $errorMessage = null;

          // POLLING STATUS: Tunggu 5 detik agar Server WA Memproses Pesan
          if (!empty($hasil['message_id'])) {
            sleep(5);
            try {
              $logDetails = $chat->getBroadcastLog($hasil['message_id']);
              if (!empty($logDetails) && isset($logDetails[0]['status'])) {
                $qontakStatus = $logDetails[0]['status'];
                if ($qontakStatus === 'failed') {
                  $errorMessage = $logDetails[0]['whatsapp_error_message'] ?? 'Message undeliverable';
                }
              }
            } catch (\Exception $e) {
              // Abaikan error saat polling, pertahankan status awal
            }
          }

          // HASIL POLLING: WA Laporan Pesan Gagal Terkirim
          if ($qontakStatus === 'failed') {
            Log::warning("Gagal kirim warning (WhatsApp API Asynchronous Failed)", [
              'customer' => $customerName,
              'customer_id' => $customer->id,
              'invoice_id' => $invoice->id,
              'error' => $errorMessage
            ]);
            $failedCount++;

            // JANGAN UPDATE `warning_sent` customer.
            DB::table('whats_log')->insert([
              'customer_id' => $customer->id,
              'jenis_pesan' => 'warning_bayar',
              'pesan' => 'Tagihan Periode ' . $bulanIni->format('F Y') . ' (Pengiriman Gagal)',
              'qontak_broadcast_id' => $hasil['message_id'] ?? null,
              'status_pengiriman' => 'failed',
              'no_tujuan' => $customer->no_hp,
              'error_message' => $errorMessage,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);

            // HASIL POLLING: SUKSES atau MASIH DI ANTRIAN
          } else {
            $updated = Customer::where('id', $customer->id)
              ->whereNull('warning_sent')
              ->update(['warning_sent' => 1]);

            if ($updated) {
              Log::info("✅ Warning berhasil diserahkan ke sistem Qontak", [
                'customer' => $customerName,
                'customer_id' => $customer->id,
                'qontak_status' => $qontakStatus
              ]);
              $sentCount++;

              // Cek apakah balikan status dari Qontak mengindikasikan final status aman / sampai
              $finalStatus = in_array(strtolower($qontakStatus), ['done', 'sent', 'delivered', 'read']) ? 'sent' : 'pending';

              // OPTIMASI LOG KETIKA MASUK ANTRIAN ATAU SUKSES
              DB::table('whats_log')->insert([
                'customer_id' => $customer->id,
                'jenis_pesan' => 'warning_bayar',
                'pesan' => 'Pesan Tagihan berjalan dengan Qontak Broadcast',
                'qontak_broadcast_id' => $hasil['message_id'] ?? null,
                'status_pengiriman' => $finalStatus,
                'no_tujuan' => $customer->no_hp,
                'error_message' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
              ]);

            } else {
              // Jika update gagal, berarti sudah di-update oleh process lain
              Log::warning("⚠️ Customer sudah di-update oleh process lain", [
                'customer' => $customerName,
                'customer_id' => $customer->id
              ]);
              $alreadySentDuringLoopCount++;
            }
          }
        }

        // Rate limiting: delay 100ms per request untuk menghindari spam
        usleep(100000); // 0.1 detik

      } catch (\Exception $e) {
        Log::error("Exception kirim warning", [
          'customer' => $customerName,
          'customer_id' => $customer->id,
          'invoice_id' => $invoice->id,
          'exception' => $e->getMessage(),
          'trace' => $e->getTraceAsString()
        ]);
        $failedCount++;
      }

      $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);

    // Log summary
    Log::info("📊 SendWarning selesai - Pengiriman Tanggal {$today->day}", [
      'tanggal' => $today->format('Y-m-d'),
      'hari_ke' => $today->day,
      'total_customers_processed' => count($customerInvoices),
      'total_customers_found' => $totalFound,
      'total_sent' => $sentCount,
      'total_failed' => $failedCount,
      'invalid_phone_skipped' => $invalidPhoneCount,
      'soft_deleted_skipped' => $softDeletedCount,
      'duplicate_prevented' => $alreadySentDuringLoopCount,
      'next_reset_date' => $today->copy()->endOfMonth()->format('Y-m-d')
    ]);

    $this->info("\n📊 SUMMARY SEND WARNING - TANGGAL {$today->day}");
    $this->info("=====================================");
    $this->info("Tanggal Kirim: {$today->format('Y-m-d')}");
    $this->info("Total Customer Tersedia: {$totalFound}");
    $this->info("Total Customer Diproses: " . count($customerInvoices));
    $this->info("📤 Status Pengiriman:");
    $this->info("  ✅ Berhasil Dikirim: {$sentCount}");
    $this->info("  ❌ Gagal Dikirim: {$failedCount}");
    $this->info("  ⏭️ Skip Status:");
    $this->info("    • Nomor HP Tidak Valid: {$invalidPhoneCount}");
    $this->info("    • Soft Deleted: {$softDeletedCount}");
    $this->info("    • Duplicate Prevented (Sudah Dikirim): {$alreadySentDuringLoopCount}");
    $this->info("🔄 Reset warning_sent akan dilakukan pada: " . $today->copy()->endOfMonth()->format('Y-m-d'));
    $this->info("=====================================");

    return Command::SUCCESS;
  }
}
