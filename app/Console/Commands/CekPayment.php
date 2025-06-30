<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Log;
use App\Services\ChatServices;

class CekPayment extends Command
{
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of due date}';
    protected $description = 'Kirim pesan WA otomatis ke pelanggan saat tanggal jatuh tempo';

    public function handle()
    {
        $startTime = microtime(true);
        Log::info('Memulai proses pengiriman pengingat pembayaran invoice');

        $tanggalHariIni = now('Asia/Jakarta')->toDateString();

        $query = Invoice::where('status_id', 7); // status belum bayar

        if (!$this->option('force')) {
            $query->whereDate('jatuh_tempo', $tanggalHariIni);
            $this->info("Memeriksa invoice dengan jatuh_tempo = {$tanggalHariIni}");
            Log::info("Memeriksa invoice dengan jatuh_tempo = {$tanggalHariIni}");
        } else {
            $this->info('Mode force diaktifkan: memeriksa semua invoice yang belum dibayar.');
            Log::info('Mode force diaktifkan: memeriksa semua invoice yang belum dibayar.');
        }

        $invoices = $query->with('customer')->get();

        if ($invoices->isEmpty()) {
            $this->info('Tidak ada invoice yang perlu diproses.');
            Log::info('Tidak ada invoice yang perlu diproses.');
            return 0;
        }

        $this->info("Ditemukan {$invoices->count()} invoice yang perlu dikirim peringatan.");
        Log::info("Ditemukan {$invoices->count()} invoice yang perlu dikirim peringatan.");

        $chatService = new ChatServices();
        $successCount = 0;
        $failedCount = 0;

        foreach ($invoices as $inv) {
            $customer = $inv->customer;

            if (!$customer || empty($customer->no_hp)) {
                $msg = "Customer ID {$inv->customer_id} tidak ditemukan atau nomor HP kosong.";
                $this->warn($msg);
                Log::warning($msg);
                continue;
            }

            try {
                $res = $chatService->kirimInvoice($customer->no_hp, $inv);

                if (isset($res['error']) && $res['error']) {
                    $msg = "Gagal kirim WA ke {$customer->no_hp}: {$res['pesan']}";
                    $this->error($msg);
                    Log::error($msg);
                    $failedCount++;
                } else {
                    $msg = "Pengingat WA berhasil dikirim ke {$customer->nama_customer}";
                    $this->info($msg);
                    Log::info($msg);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $msg = "Exception saat kirim WA customer ID {$customer->id}: " . $e->getMessage();
                $this->error($msg);
                Log::error($msg);
                $failedCount++;
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        $summary = "Selesai. WA terkirim: {$successCount}, Gagal: {$failedCount}, Durasi: {$executionTime} detik.";
        $this->info($summary);
        Log::info($summary);
        return 0;
    }
}
