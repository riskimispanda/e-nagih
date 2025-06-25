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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of due date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blokir Otomatis Jika Belum Bayar pada Tanggal Jatuh Tempo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        Log::info('Memulai proses pengecekan pembayaran invoice');
        
        // Ambil semua invoice yang statusnya belum bayar (status_id 7) dan sudah lewat jatuh tempo
        $jakartaTime = now()->setTimezone('Asia/Jakarta');
        $query = Invoice::where('status_id', 7);
        
        // Jika tidak menggunakan opsi force, hanya cek invoice dengan jatuh tempo yang sudah lewat
        if (!$this->option('force')) {
            // Menggunakan where dengan operator <= untuk membandingkan tanggal dan waktu secara lengkap
            // Ini akan mengambil semua invoice yang jatuh tempo sudah lewat atau sama dengan waktu sekarang
            $query->where('jatuh_tempo', '<=', $jakartaTime->toDateTimeString());
            $this->info('Memeriksa invoice dengan jatuh tempo sebelum atau sama dengan: ' . $jakartaTime->toDateTimeString());
            Log::info('Memeriksa invoice dengan jatuh tempo sebelum atau sama dengan: ' . $jakartaTime->toDateTimeString());
        } else {
            $this->info('Mode force diaktifkan: memeriksa semua invoice yang belum dibayar.');
            Log::info('Mode force diaktifkan: memeriksa semua invoice yang belum dibayar.');
        }
        
        $invoices = $query->with('customer')->get();

        if ($invoices->isEmpty()) {
            $message = 'Tidak ada invoice yang perlu diproses.';
            $this->info($message);
            Log::info($message);
            return 0;
        }

        $this->info("Ditemukan {$invoices->count()} invoice yang perlu diproses.");
        Log::info("Ditemukan {$invoices->count()} invoice yang perlu diproses.");

        $mikrotik = new MikrotikServices();
        $successCount = 0;
        $failedCount = 0;
        
        // Simpan ID customer yang sudah diblokir sebelumnya
        $alreadyBlockedCustomers = Customer::where('status_id', 9)->pluck('id')->toArray();
        $this->info("Ditemukan " . count($alreadyBlockedCustomers) . " customer yang sudah berstatus blokir.");
        Log::info("Ditemukan " . count($alreadyBlockedCustomers) . " customer yang sudah berstatus blokir.");

        foreach ($invoices as $inv) {
            $customer = $inv->customer;

            if (!$customer) {
                $message = "Customer dengan ID {$inv->customer_id} tidak ditemukan.";
                $this->warn($message);
                Log::warning($message);
                continue;
            }

            if (empty($customer->usersecret)) {
                $message = "Customer ID {$customer->id} tidak memiliki usersecret untuk blokir.";
                $this->warn($message);
                Log::warning($message);
                continue;
            }

            // Proses blokir user lewat MikrotikService
            try {
                // Cek apakah usersecret sudah ada di profile ISOLIREBILLING
                $userProfileData = $mikrotik->userProfile($customer->usersecret);
                
                // Periksa apakah data ditemukan dan cek profile
                if (empty($userProfileData)) {
                    $message = "User secret {$customer->usersecret} tidak ditemukan di Mikrotik.";
                    $this->warn($message);
                    Log::warning($message);
                    continue;
                }
                
                // Ambil profile dari data yang dikembalikan
                $currentProfile = isset($userProfileData[0]['profile']) ? $userProfileData[0]['profile'] : null;
                
                // Cek status customer saat ini
                $wasAlreadyBlocked = in_array($customer->id, $alreadyBlockedCustomers);
                
                if ($currentProfile !== 'ISOLIREBILLING') {
                    // Jika belum, ubah profile ke ISOLIREBILLING dan remove active connections
                    $mikrotik->changeUserProfile($customer->usersecret, 'ISOLIREBILLING');
                    $mikrotik->removeActiveConnections($customer->usersecret);
                    $message = "Profile Customer {$customer->nama_customer} diubah ke ISOLIREBILLING dan di Remove dari Active Connections.";
                    $this->info($message);
                    Log::info($message);
                } else {
                    $message = "Customer {$customer->nama_customer} sudah dalam profile ISOLIREBILLING.";
                    $this->info($message);
                    Log::info($message);
                }
                
                // Update status customer ke status blokir (ID 9)
                $customer->update(['status_id' => 9]); // Status Blokir (blokir otomatis jika belum bayar pada hari jatuh tempo)
                
                // Update paket invoice ke paket ID 8
                $inv->update(['paket_id' => 8]);
                
                // Hanya kirim notifikasi jika customer belum diblokir sebelumnya
                if (!$wasAlreadyBlocked) {
                    // Broadcast event untuk notifikasi real-time
                    broadcast(new UpdateBaru(
                        $customer->toArray(), 
                        'danger', 
                        "Pelanggan {$customer->nama_customer} telah diblokir karena belum membayar Tagihan."
                    ));
                    $message = "Customer ID {$customer->id} ({$customer->nama_customer}) berhasil diblokir dan notifikasi dikirim.";
                } else {
                    $message = "Customer ID {$customer->id} ({$customer->nama_customer}) berhasil diblokir (tanpa notifikasi karena sudah diblokir sebelumnya).";
                }
                
                $this->info($message);
                Log::info($message);
                $successCount++;
            } catch (\Exception $e) {
                $message = "Gagal blokir customer ID {$customer->id}: " . $e->getMessage();
                $this->error($message);
                Log::error($message);
                $failedCount++;
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        $summary = "Proses cek payment selesai. Berhasil: {$successCount}, Gagal: {$failedCount}, Waktu eksekusi: {$executionTime} detik.";
        $this->info($summary);
        Log::info($summary);
        
        return 0;
    }
}
