<?php

namespace App\Console\Commands;

use App\Services\QontakServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncQontakStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qontak:sync-status {--limit=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi status pengiriman WhatsApp dari API Mekari Qontak';

    /**
     * Execute the console command.
     */
    public function handle(QontakServices $qontakService)
    {
        $limit = $this->option('limit');
        $this->info("Memulai sinkronisasi status Qontak (limit: $limit)...");

        try {
            $updated = $qontakService->syncAllPendingLogs($limit);

            $this->info("Sinkronisasi selesai. $updated log berhasil diperbarui.");
            Log::info("Qontak Sync Command: $updated logs updated.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal sinkronisasi: " . $e->getMessage());
            Log::error("Qontak Sync Command Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
