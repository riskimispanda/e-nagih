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
Schedule::command('app:send-warning')
      ->monthlyOn(2, '00:01')
      ->timezone('Asia/Jakarta')
      ->description('Send Warning Tagihan');
