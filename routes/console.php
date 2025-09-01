<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::command('app:cek-payment')->everyMinute();
Schedule::command('app:generate-invoice')->everyMinute();
Schedule::command('app:send-warning')->dailyAt('08:00');