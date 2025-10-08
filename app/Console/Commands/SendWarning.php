<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ChatServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class SendWarning extends Command
{
    protected $signature = 'app:send-warning';
    protected $description = 'Kirim pesan warning jika pelanggan belum bayar setelah jatuh tempo (hanya tgl 1 dan 10)';

    public function handle()
    {
        $today = Carbon::today();

        // Reset warning_sent pada tanggal 2 dan 11
        if (in_array($today->day, [2, 11])) {
            $count = Customer::whereNotNull('warning_sent')->update(['warning_sent' => null]);
            Log::info("Berhasil reset field warning_sent. Total customer direset: " . $count);
            return Command::SUCCESS;
        }

        // hanya jalan tanggal 1 dan 10
        if (!in_array($today->day, [1, 10])) {
            return Command::SUCCESS;
        }

        $chat = new ChatServices();

        // Get unpaid invoices grouped by customer with latest due date - INCLUDE SOFT DELETED CUSTOMERS
        $invoices = Invoice::with(['customer' => function ($query) {
            $query->withTrashed(); // Include soft deleted customers
        }])
            ->where('status_id', 7)
            ->whereDate('jatuh_tempo', '<', $today)
            ->where('paket_id', '!=', 11)
            ->whereHas('customer', function ($query) {
                $query->withTrashed(); // Include soft deleted customers in whereHas
            })
            ->get()
            ->groupBy('customer_id')
            ->map(function ($customerInvoices) {
                return $customerInvoices->sortByDesc('jatuh_tempo')->first();
            });

        $softDeletedCount = 0;
        $sentCount = 0;
        $failedCount = 0;

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }

            // Check if customer is soft deleted
            if ($invoice->customer->trashed()) {
                Log::info("⏭️ Skip customer soft deleted untuk warning: " . $invoice->customer->nama_customer, [
                    'customer_id' => $invoice->customer->id,
                    'invoice_id' => $invoice->id,
                    'deleted_at' => $invoice->customer->deleted_at
                ]);
                $this->warn("⏭️ Customer sudah dihapus (soft delete): {$invoice->customer->nama_customer} (ID: {$invoice->customer->id})");
                $softDeletedCount++;
                continue;
            }

            // Cek jika sudah dikirim (warning_sent = 1)
            if ($invoice->customer->warning_sent == 1) {
                Log::info($invoice->customer->nama_customer . ' sudah di kirim notifikasi');
                continue;
            }

            $hasil = $chat->kirimWarningBayar($invoice->customer, $invoice);

            if (isset($hasil['success']) && $hasil['success'] === false) {
                $this->warn("⚠️ {$hasil['message']}");
                $failedCount++;
            } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                $this->error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
                Log::error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
                $failedCount++;
            } else {
                $invoice->customer->update(['warning_sent' => 1]);
                $this->info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
                Log::info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
                $sentCount++;
            }
        }

        // Log summary
        Log::info("📊 SendWarning selesai (Tanggal {$today->day})", [
            'total_invoices' => $invoices->count(),
            'soft_deleted_skipped' => $softDeletedCount,
            'successfully_sent' => $sentCount,
            'failed' => $failedCount,
            'date' => $today->format('Y-m-d')
        ]);

        $this->info("📊 SendWarning selesai (Tanggal {$today->day}): {$sentCount} berhasil, {$failedCount} gagal, {$softDeletedCount} skip (soft delete)");

        return Command::SUCCESS;
    }
}