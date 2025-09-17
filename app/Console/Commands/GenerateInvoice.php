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

        $pelangganAktif = Customer::where('status_id', 3)->get();

        foreach ($pelangganAktif as $customer) {
            $invoiceTerakhir = Invoice::where('customer_id', $customer->id)
                ->orderByDesc('created_at')
                ->first();

            if (!$invoiceTerakhir) {
                $this->info("⛔ {$customer->nama_customer} belum punya invoice. Lewati.");
                continue;
            }

            // Gunakan status_id untuk cek pembayaran (8 = sudah bayar)
            if ($invoiceTerakhir->status_id != 8) {
                $this->warn("❌ {$customer->nama_customer} status invoice terakhir belum dibayar (status_id = {$invoiceTerakhir->status_id}). Lewati.");
                continue;
            }

            // Cek apakah sudah ada invoice bulan depan
            $sudahAda = Invoice::where('customer_id', $customer->id)
                ->whereMonth('jatuh_tempo', $jatuhTempoBulanDepan->month)
                ->whereYear('jatuh_tempo', $jatuhTempoBulanDepan->year)
                ->exists();

            if ($sudahAda) {
                $this->info("✅ Invoice bulan depan sudah ada untuk {$customer->nama_customer}. Lewati.");
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
        }

        $this->info("🎯 Selesai proses generate invoice.");
    }
}
