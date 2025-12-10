<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Router;
use App\Jobs\CacheConnectionMikrotik;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        // Jalankan pengecekan pembayaran setiap hari pada pukul 00:05 WIB (GMT+7)
        $schedule->command('app:cek-payment')
            ->monthlyOn(10, '00:01')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/cek-payment.log'));

        $schedule->command('app:test-command')->everyMinute();
        $schedule->command('app:generate-invoice')
            ->monthlyOn(1, '00:01')
            ->timezone('Asia/Jakarta') // Sesuaikan timezone
            ->description('Generate invoice untuk bulan depan');
        // $schedule->command('app:send-warning')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
