<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use Illuminate\Support\Facades\Log;
use App\Services\ChatServices;

class CekPayment extends Command
{
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of due date}';
    protected $description = 'Cek invoice: kirim WA jika jatuh tempo, blokir otomatis jika lewat tanggal_blokir';

    public function handle()
    {
        $startTime = microtime(true);
        Log::info('🚀 Memulai pengecekan invoice');

        $tanggalHariIni = now('Asia/Jakarta')->toDateString();
        $query = Invoice::where('status_id', 7); // status_id 7 = Belum Bayar

        if (!$this->option('force')) {
            $query->where(function ($q) use ($tanggalHariIni) {
                $q->whereDate('jatuh_tempo', $tanggalHariIni)
                  ->orWhereDate('tanggal_blokir', '<=', $tanggalHariIni);
            });

            $this->info("🔍 Mode normal: cek jatuh_tempo = {$tanggalHariIni} atau >= tanggal_blokir");
            Log::info("🔍 Mode normal: jatuh_tempo = {$tanggalHariIni} atau >= tanggal_blokir");
        } else {
            $this->info('⚠️ Mode force aktif: proses semua invoice belum bayar.');
            Log::info('⚠️ Mode force aktif.');
        }

        $invoices = $query->with('customer.router')->get();

        if ($invoices->isEmpty()) {
            $this->info('✅ Tidak ada invoice yang perlu diproses.');
            Log::info('✅ Tidak ada invoice yang perlu diproses.');
            return 0;
        }

        $this->info("📦 Total invoice ditemukan: {$invoices->count()}");
        Log::info("📦 Ditemukan {$invoices->count()} invoice untuk diproses.");

        $chatService = new ChatServices();
        $successCount = 0;
        $failedCount = 0;
        $blockedCount = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;

            if (!$customer || empty($customer->no_hp)) {
                $this->warn("⚠️ Customer ID {$invoice->customer_id} tidak ditemukan atau nomor HP kosong.");
                Log::warning("⚠️ Customer ID {$invoice->customer_id} tidak ditemukan atau nomor HP kosong.");
                continue;
            }

            $router = $customer->router;
            if (!$router) {
                $this->warn("⚠️ Router tidak ditemukan untuk {$customer->nama_customer}");
                Log::warning("⚠️ Router tidak ditemukan untuk {$customer->nama_customer}");
                continue;
            }

            try {
                $client = MikrotikServices::connect($router);
            } catch (\Exception $e) {
                Log::error("❌ Gagal konek ke router {$router->nama_router}: " . $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine());
                continue;
            }

            // 🔒 Blokir otomatis jika hari ini >= tanggal_blokir
            $tanggalBlokir = \Carbon\Carbon::parse($invoice->jatuh_tempo)
                ->addMonth() // bulan setelah jatuh tempo
                ->day((int) ($invoice->tanggal_blokir)); // default 10 jika null

            if ($tanggalHariIni >= $tanggalBlokir) {
                if ($customer->status_id == 9) {
                    $this->info("⚠️ Customer sudah diblokir: {$customer->nama_customer}");
                    continue;
                }

                $blok = MikrotikServices::changeUserProfile($client, $customer->usersecret, 'ISOLIREBILLING');

                if ($blok) {
                    $customer->status_id = 9;
                    $customer->save();
                    $chatService->kirimNotifikasiBlokir($customer->no_hp, $invoice);

                    $removed = MikrotikServices::removeActiveConnections($client, $customer->usersecret);
                    if ($removed) {
                        $this->info("🔒 Blokir sukses, koneksi aktif dihapus: {$customer->nama_customer}");
                        Log::info("🔒 Blokir sukses & koneksi dihapus: {$customer->nama_customer}");
                    } else {
                        $this->warn("⚠️ Blokir sukses, tapi gagal hapus koneksi aktif: {$customer->nama_customer}");
                        Log::warning("⚠️ Blokir sukses, tapi koneksi aktif gagal dihapus: {$customer->nama_customer}");
                    }

                    $this->info("✅ Status customer diupdate ke 9 (Diblokir)");
                    $blockedCount++;
                } else {
                    $this->error("❌ Gagal ubah profil PPP / blokir: {$customer->nama_customer}");
                    Log::error("❌ Gagal ubah profil PPP: {$customer->nama_customer}");
                }
                continue;
            }


            // 📩 Kirim WA jika hari ini jatuh tempo
            if ($tanggalHariIni == $invoice->jatuh_tempo) {
                try {
                    $res = $chatService->kirimInvoice($customer->no_hp, $invoice);

                    if (isset($res['error']) && $res['error']) {
                        $msg = "❌ Gagal kirim WA ke {$customer->no_hp}: {$res['pesan']}";
                        $this->error($msg);
                        Log::error($msg);
                        $failedCount++;
                    } else {
                        $msg = "📩 WA berhasil dikirim ke {$customer->nama_customer}";
                        $this->info($msg);
                        Log::info($msg);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $msg = "❌ Exception kirim WA ke {$customer->id}: " . $e->getMessage();
                    $this->error($msg);
                    Log::error($msg);
                    $failedCount++;
                }
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $summary = "📊 Selesai | WA sukses: {$successCount}, gagal: {$failedCount}, diblokir: {$blockedCount}, durasi: {$duration}s";

        $this->info($summary);
        Log::info($summary);

        return 0;
    }
}
