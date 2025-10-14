<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class GenerateInvoice extends Command
{
    protected $signature = 'app:generate-invoice';
    protected $description = 'Buat invoice bulan depan jika invoice terakhir statusnya sudah dibayar (status_id = 8)';

    public function handle()
    {
        $bulanDepan = Carbon::now()->addMonth();
        $jatuhTempoBulanDepan = $bulanDepan->endOfMonth();

        // Hanya ambil customer aktif yang TIDAK di-soft delete
        $pelangganAktif = Customer::whereIn('status_id', [3, 4, 9])
            ->whereNull('deleted_at') // Pastikan tidak soft deleted
            ->get();

        $softDeletedCount = 0;
        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($pelangganAktif as $customer) {
            // Double check: Skip jika customer di-soft delete
            if ($customer->trashed()) {
                $this->warn("⏭️ Customer sudah dihapus (soft delete): {$customer->nama_customer}");
                $softDeletedCount++;
                continue;
            }

            $invoiceTerakhir = Invoice::where('customer_id', $customer->id)
                ->orderByDesc('created_at')
                ->first();

            if (!$invoiceTerakhir) {
                $this->info("⛔ {$customer->nama_customer} belum punya invoice. Lewati.");
                $skippedCount++;
                continue;
            }

            // Cek apakah sudah ada invoice bulan depan
            $sudahAda = Invoice::where('customer_id', $customer->id)
                ->whereMonth('jatuh_tempo', $jatuhTempoBulanDepan->month)
                ->whereYear('jatuh_tempo', $jatuhTempoBulanDepan->year)
                ->exists();

            if ($sudahAda) {
                $this->info("✅ Invoice bulan depan sudah ada untuk {$customer->nama_customer}. Lewati.");
                $skippedCount++;
                continue;
            }

            // Generate Merchant Reference 
            $merchant = 'INV-' . $customer->id . '-' . time();

            // Buat invoice bulan depan
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'paket_id'    => $customer->paket_id,
                'status_id'   => 7, // Belum Dibayar
                'tagihan'     => $customer->paket->harga,
                'merchant_ref' => $merchant,
                'jatuh_tempo' => $jatuhTempoBulanDepan,
            ]);

            $this->info("✅ Invoice dibuat untuk {$customer->nama_customer} | Rp " . number_format($invoice->tagihan, 0, ',', '.') . " | Jatuh Tempo: " . $invoice->jatuh_tempo->format('d-m-Y'));
            $generatedCount++;
        }

        // Summary
        $this->info("\n🎯 SUMMARY GENERATE INVOICE:");
        $this->info("✅ {$generatedCount} invoice berhasil dibuat");
        $this->info("⏭️ {$skippedCount} customer dilewati");
        $this->info("🗑️ {$softDeletedCount} customer soft deleted");
        $this->info("📊 Total customer diproses: " . $pelangganAktif->count());
    }
}