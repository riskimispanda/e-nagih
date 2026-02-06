<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule::command('app:cek-payment')
//     ->dailyAt('00:01')
//     ->timezone('Asia/Jakarta')
//     ->description('Cek invoice dan blokir otomatis');
//
// Kirim warning tanggal 2 setiap bulan jam 08:00
// Schedule::command('app:send-warning')
//   ->monthlyOn(2, '08:00')
//   ->timezone('Asia/Jakarta')
//   ->description('Send Warning Tagihan - Tanggal 2');

// // Kirim warning tanggal 3 setiap bulan jam 08:00
// Schedule::command('app:send-warning')
//   ->monthlyOn(3, '08:00')
//   ->timezone('Asia/Jakarta')
//   ->description('Send Warning Tagihan - Tanggal 3');
