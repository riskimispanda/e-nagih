<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Router;
use App\Services\MikrotikServices;
use RouterOS\Query;
use RouterOS\Client;
use Illuminate\Support\Facades\Log;

class CacheConnectionMikrotik implements ShouldQueue
{
    use Queueable;

    protected $routerId;

    /**
     * Buat job baru dengan parameter router.
     */
    public function __construct(int $routerId)
    {
        $this->routerId = $routerId;
    }

    /**
     * Jalankan job.
     */
    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);

        try {
            // Pakai koneksi cache dari MikrotikServices
            $client = MikrotikServices::connect($router);

            // Contoh query ringan untuk memastikan koneksi aktif
            $query = new Query('/system/identity/print');
            $result = $client->query($query)->read();

            Log::info("✅ Cache koneksi Mikrotik berhasil untuk router: {$router->nama_router}", $result);
        } catch (\Throwable $e) {
            Log::error("❌ Gagal cache koneksi router {$router->nama_router}: " . $e->getMessage());
        }
    }
}