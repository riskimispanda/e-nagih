<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\QontakServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWarning extends Command
{
  protected $signature = 'app:send-warning';
  protected $description = 'Kirim pesan warning untuk tagihan bulan lalu (kirim tanggal 2-3, 900/hari, reset akhir bulan)';

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
    if (!in_array($today->day, [2, 3, 6])) {
      $this->info("⏭️ Command hanya berjalan pada tanggal 2-3 (kirim) dan akhir bulan (reset). Hari ini tanggal {$today->day}.");
      Log::info("SendWarning skipped - hanya berjalan tanggal 2-3 & akhir bulan", ['tanggal' => $today->format('Y-m-d')]);
      return Command::SUCCESS;
    }

    $bulanLalu = $today->copy()->subMonth();
    $awalBulanLalu = $bulanLalu->copy()->startOfMonth();
    $akhirBulanLalu = $bulanLalu->copy()->endOfMonth();

    $this->info("🔍 Mencari tagihan bulan lalu: {$bulanLalu->format('F Y')}");
    $this->info("📅 Periode: {$awalBulanLalu->format('d M Y')} - {$akhirBulanLalu->format('d M Y')}");
    $this->info("💡 Info: Reset warning_sent akan dilakukan pada akhir bulan");

    $chat = new QontakServices();

    // Get unpaid invoices dari bulan lalu (tanpa batasan tahun)
    // Contoh: Jika sekarang Jan 2026, akan ambil Des 2025 (tahun lalu)
    $invoices = Invoice::with(['customer']) // Eager loading untuk optimasi
      ->where('status_id', 7) // Belum bayar
      ->whereMonth('jatuh_tempo', Carbon::now()->month) // Bulan lalu (apapun tahunnya)
      ->whereYear('jatuh_tempo', Carbon::now()->year) // Bulan lalu (apapun tahunnya)
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
    $this->info("📊 Total customer dengan tagihan bulan lalu yang belum dibayar & belum dikirim: {$totalFound}");

    if ($totalFound === 0) {
      $this->info("✅ Tidak ada tagihan bulan lalu yang perlu dikirim warning.");
      Log::info("Tidak ada tagihan yang perlu dikirim", [
        'periode' => $bulanLalu->format('F Y'),
        'periode_mulai' => $awalBulanLalu->format('Y-m-d'),
        'periode_akhir' => $akhirBulanLalu->format('Y-m-d'),
        'tanggal' => $today->day
      ]);
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

        if (isset($hasil['success']) && $hasil['success'] === false) {
          Log::warning("Gagal kirim warning", [
            'customer' => $customerName,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'error' => $hasil['message'] ?? 'Unknown error'
          ]);
          $failedCount++;
        } elseif (isset($hasil['error']) && $hasil['error'] === true) {
          Log::error("Error kirim warning", [
            'customer' => $customerName,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'error' => $hasil['pesan'] ?? 'Unknown error'
          ]);
          $failedCount++;
        } else {
          // PROTEKSI LAYER 2: Atomic update dengan WHERE condition
          // Update hanya jika warning_sent masih NULL (mencegah race condition)
          $updated = Customer::where('id', $customer->id)
            ->whereNull('warning_sent')
            ->update(['warning_sent' => 1]);

          if ($updated) {
            Log::info("✅ Warning berhasil dikirim", [
              'customer' => $customerName,
              'customer_id' => $customer->id,
              'invoice_id' => $invoice->id,
              'periode_tagihan' => $bulanLalu->format('F Y'),
              'jatuh_tempo' => $invoice->jatuh_tempo,
              'tanggal_kirim' => $today->format('Y-m-d')
            ]);
            $sentCount++;
          } else {
            // Jika update gagal, berarti sudah di-update oleh process lain
            Log::warning("⚠️ Customer sudah di-update oleh process lain", [
              'customer' => $customerName,
              'customer_id' => $customer->id,
              'invoice_id' => $invoice->id,
              'reason' => 'Atomic update failed - warning_sent sudah tidak NULL'
            ]);
            $alreadySentDuringLoopCount++;
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
      'periode_tagihan' => $bulanLalu->format('F Y'),
      'periode_mulai' => $awalBulanLalu->format('Y-m-d'),
      'periode_akhir' => $akhirBulanLalu->format('Y-m-d'),
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
    $this->info("Periode Tagihan: {$bulanLalu->format('F Y')}");
    $this->info("Range: {$awalBulanLalu->format('d M Y')} - {$akhirBulanLalu->format('d M Y')}");
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
