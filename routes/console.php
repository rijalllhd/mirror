<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sensor:send-daily-recap')
    ->dailyAt('20:00')
    ->timezone('Asia/Jakarta')
    ->name('wa-recap-harian')
    ->withoutOverlapping();

Schedule::command('sensor:check-alerts')
    ->everyTenSeconds()
    ->timezone('Asia/Jakarta')
    ->name('wa-peringatan-sensor')
    ->withoutOverlapping();
