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
    protected $description = 'Kirim pesan warning untuk tagihan bulan lalu (kirim tanggal 2, reset akhir bulan)';

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
        // BAGIAN 2: KIRIM PESAN PADA TANGGAL 2
        // ============================================
        if ($today->day != 2) {
            $this->info("⏭️ Command hanya berjalan pada tanggal 2 (kirim) dan akhir bulan (reset). Hari ini tanggal {$today->day}.");
            Log::info("SendWarning skipped - hanya berjalan tanggal 2 & akhir bulan", ['tanggal' => $today->format('Y-m-d')]);
            return Command::SUCCESS;
        }

        $bulanLalu = $today->copy()->subMonth();
        $tahunBulanLalu = $bulanLalu->year;
        $bulanBulanLalu = $bulanLalu->month;

        $this->info("🔍 Mencari tagihan bulan: {$bulanBulanLalu}/{$tahunBulanLalu}");
        $this->info("💡 Info: Reset warning_sent akan dilakukan pada akhir bulan");

        $chat = new QontakServices();

        // Get unpaid invoices dari bulan lalu saja
        $invoiceGroups = Invoice::where('status_id', 7) // Belum bayar
            ->whereMonth('jatuh_tempo', $bulanBulanLalu) // Jatuh tempo bulan lalu
            ->whereYear('jatuh_tempo', $tahunBulanLalu) // Tahun bulan lalu
            ->where('paket_id', '!=', 11) // Bukan paket khusus
            ->whereHas('customer', function ($query) {
                $query->whereNull('deleted_at'); // Hanya customer aktif
            })
            ->select('customer_id', 'id', 'jatuh_tempo')
            ->get()
            ->groupBy('customer_id');

        // Ambil invoice terbaru per customer
        $customerInvoices = [];
        foreach ($invoiceGroups as $customerId => $invoices) {
            $latestInvoice = $invoices->sortByDesc('jatuh_tempo')->first();
            $customerInvoices[$customerId] = [
                'invoice_id' => $latestInvoice->id,
                'jatuh_tempo' => $latestInvoice->jatuh_tempo
            ];
        }

        $this->info("📊 Total customer dengan tagihan bulan lalu yang belum dibayar: " . count($customerInvoices));

        $limit = 50;
        $customerInvoices = array_slice($customerInvoices, 0, $limit, true);

        $this->info("📊 Mengirim ke {$limit} customer pertama (percobaan)");

        $softDeletedCount = 0;
        $sentCount = 0;
        $failedCount = 0;
        $alreadySentCount = 0;

        if (empty($customerInvoices)) {
            $this->info("✅ Tidak ada tagihan bulan lalu yang belum dibayar.");
            Log::info("Tidak ada tagihan bulan lalu yang belum dibayar", [
                'bulan' => $bulanBulanLalu,
                'tahun' => $tahunBulanLalu
            ]);
            return Command::SUCCESS;
        }

        foreach ($customerInvoices as $customerId => $invoiceData) {
            // Ambil customer FRESH dari database
            $customer = Customer::find($customerId);

            if (!$customer) {
                $this->warn("⚠️ Customer ID {$customerId} tidak ditemukan");
                continue;
            }

            // Ambil invoice FRESH dari database
            $invoice = Invoice::find($invoiceData['invoice_id']);

            if (!$invoice) {
                $this->warn("⚠️ Invoice ID {$invoiceData['invoice_id']} tidak ditemukan untuk customer {$customer->nama_customer}");
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
                continue;
            }

            // CEK JIKA SUDAH DIKIRIM SEBELUMNYA
            if ($customer->warning_sent == 1) {
                $this->info("⏭️ Skip {$customerName} - sudah dikirim warning sebelumnya");
                Log::info("Customer sudah memiliki warning_sent = 1", [
                    'customer' => $customerName,
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'reason' => 'Sudah dikirim warning bulan ini'
                ]);
                $alreadySentCount++;
                continue;
            }

            // Kirim warning
            try {
                $hasil = $chat->notifTagihan($customer->no_hp, $invoice);

                if (isset($hasil['success']) && $hasil['success'] === false) {
                    $this->warn("⚠️ Gagal untuk {$customerName}: {$hasil['message']}");
                    Log::warning("Gagal kirim warning", [
                        'customer' => $customerName,
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'error' => $hasil['message'] ?? 'Unknown error'
                    ]);
                    $failedCount++;
                } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                    $this->error("❌ Error untuk {$customerName}: {$hasil['pesan']}");
                    Log::error("Error kirim warning", [
                        'customer' => $customerName,
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'error' => $hasil['pesan'] ?? 'Unknown error'
                    ]);
                    $failedCount++;
                } else {
                    // Update status warning_sent
                    $customer->update(['warning_sent' => 1]);

                    $this->info("✅ Berhasil dikirim ke {$customerName} untuk invoice {$invoice->id}");
                    Log::info("✅ Warning berhasil dikirim", [
                        'customer' => $customerName,
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'tagihan_bulan' => "{$bulanBulanLalu}/{$tahunBulanLalu}",
                        'jatuh_tempo' => $invoiceData['jatuh_tempo'],
                        'tanggal_kirim' => $today->format('Y-m-d')
                    ]);
                    $sentCount++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Exception untuk {$customerName}: " . $e->getMessage());
                Log::error("Exception kirim warning", [
                    'customer' => $customerName,
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $failedCount++;
            }
        }

        // Log summary
        Log::info("📊 SendWarning selesai - Pengiriman Tanggal 2", [
            'tanggal' => $today->format('Y-m-d'),
            'tagihan_bulan' => "{$bulanBulanLalu}/{$tahunBulanLalu}",
            'total_customers_found' => count($customerInvoices),
            'total_sent' => $sentCount,
            'total_failed' => $failedCount,
            'already_sent_skipped' => $alreadySentCount,
            'soft_deleted_skipped' => $softDeletedCount,
            'next_reset_date' => $today->copy()->endOfMonth()->format('Y-m-d')
        ]);

        $this->info("\n📊 SUMMARY SEND WARNING - TANGGAL 2");
        $this->info("=====================================");
        $this->info("Tanggal Kirim: {$today->format('Y-m-d')}");
        $this->info("Tagihan Bulan: {$bulanBulanLalu}/{$tahunBulanLalu}");
        $this->info("Total Customer Ditemukan: " . count($customerInvoices));
        $this->info("📤 Status Pengiriman:");
        $this->info("  ✅ Berhasil Dikirim: {$sentCount}");
        $this->info("  ❌ Gagal Dikirim: {$failedCount}");
        $this->info("  ⏭️ Skip Status:");
        $this->info("    • Sudah Dikirim Sebelumnya: {$alreadySentCount}");
        $this->info("    • Soft Deleted: {$softDeletedCount}");
        $this->info("🔄 Reset warning_sent akan dilakukan pada: " . $today->copy()->endOfMonth()->format('Y-m-d'));
        $this->info("=====================================");

        return Command::SUCCESS;
    }
}
