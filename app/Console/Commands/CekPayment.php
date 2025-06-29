<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Log;

class CekPayment extends Command
{
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of block date}';
    protected $description = 'Blokir Otomatis Jika Belum Bayar pada Tanggal Blokir';

    public function handle()
    {
        $startTime = microtime(true);
        Log::info('Memulai proses pengecekan pembayaran invoice');
        $jakartaTime = now()->setTimezone('Asia/Jakarta');
        $tanggalHariIni = $jakartaTime->day;

        $query = Invoice::where('status_id', 7);
        if (!$this->option('force')) {
            $query->where('tanggal_blokir', $tanggalHariIni);
            $this->info('Memeriksa invoice dengan tanggal_blokir = ' . $tanggalHariIni);
            Log::info('Memeriksa invoice dengan tanggal_blokir = ' . $tanggalHariIni);
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

        $this->info("Ditemukan {$invoices->count()} invoice yang perlu diproses.");
        Log::info("Ditemukan {$invoices->count()} invoice yang perlu diproses.");

        $mikrotik = new MikrotikServices();
        $successCount = 0;
        $failedCount = 0;
        $alreadyBlockedCustomers = Customer::where('status_id', 9)->pluck('id')->toArray();
        $this->info("Ditemukan " . count($alreadyBlockedCustomers) . " customer yang sudah berstatus blokir.");
        Log::info("Ditemukan " . count($alreadyBlockedCustomers) . " customer yang sudah berstatus blokir.");

        foreach ($invoices as $inv) {
            $customer = $inv->customer;

            if (!$customer) {
                $msg = "Customer dengan ID {$inv->customer_id} tidak ditemukan.";
                $this->warn($msg);
                Log::warning($msg);
                continue;
            }

            if (empty($customer->usersecret)) {
                $msg = "Customer ID {$customer->id} tidak memiliki usersecret untuk blokir.";
                $this->warn($msg);
                Log::warning($msg);
                continue;
            }
            try {
                $userProfileData = $mikrotik->userProfile($customer->usersecret);
                if (empty($userProfileData)) {
                    $msg = "User secret {$customer->usersecret} tidak ditemukan di Mikrotik.";
                    $this->warn($msg);
                    Log::warning($msg);
                    continue;
                }
                $currentProfile = $userProfileData[0]['profile'] ?? null;
                $wasAlreadyBlocked = in_array($customer->id, $alreadyBlockedCustomers);
                if ($currentProfile !== 'ISOLIREBILLING') {
                    $mikrotik->changeUserProfile($customer->usersecret, 'ISOLIREBILLING');
                    $mikrotik->removeActiveConnections($customer->usersecret);
                    $msg = "Customer {$customer->nama_customer} diubah ke ISOLIREBILLING dan koneksi aktif dihapus.";
                    $this->info($msg);
                    Log::info($msg);
                } else {
                    $msg = "Customer {$customer->nama_customer} sudah dalam profile ISOLIREBILLING.";
                    $this->info($msg);
                    Log::info($msg);
                }
                $customer->update(['status_id' => 9]); // Status blokir
                $inv->update(['paket_id' => 8]); // Paket isolir

                if (!$wasAlreadyBlocked) {
                    broadcast(new UpdateBaru(
                        $customer->toArray(),
                        'danger',
                        "Pelanggan {$customer->nama_customer} telah diblokir karena belum membayar tagihan."
                    ));
                    $msg = "Customer {$customer->id} ({$customer->nama_customer}) diblokir dan notifikasi dikirim.";
                } else {
                    $msg = "Customer {$customer->id} ({$customer->nama_customer}) diblokir (tanpa notifikasi).";
                }
                $this->info($msg);
                Log::info($msg);
                $successCount++;
            } catch (\Exception $e) {
                $msg = "Gagal blokir customer ID {$customer->id}: " . $e->getMessage();
                $this->error($msg);
                Log::error($msg);
                $failedCount++;
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        $summary = "Selesai. Berhasil: {$successCount}, Gagal: {$failedCount}, Durasi: {$executionTime} detik.";
        $this->info($summary);
        Log::info($summary);
        return 0;
    }
}
