<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        // Jalankan pengecekan pembayaran setiap hari pada pukul 00:05 WIB (GMT+7)
        $schedule->command('app:cek-payment')
            ->dailyAt('00:05')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/cek-payment.log'));
            
        // Jalankan pengecekan pembayaran dengan mode force setiap minggu pada hari Senin pukul 08:00 WIB
        $schedule->command('app:cek-payment --force')
            ->weeklyOn(1, '08:00') // 1 = Senin
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/cek-payment-force.log'));
            
        $schedule->command('app:test-command')->everyMinute();
        $schedule->command('app:generate-invoice')->everyMinute();
        $schedule->command('app:send-warning')->dailyAt('08:00');
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
