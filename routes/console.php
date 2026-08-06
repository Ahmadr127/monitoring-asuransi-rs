<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule untuk update expired contracts setiap hari pada jam 00:01
Schedule::command('contracts:update-expired')->dailyAt('00:01');

// Schedule untuk pengiriman reminder kontrak (H-90, H-30, H-7) setiap hari jam 08:00
Schedule::command('app:send-contract-reminders')->dailyAt('08:00');

// Schedule untuk generate notifikasi kontrak setiap jam
Schedule::command('contracts:generate-notifications')->hourly();

// Schedule untuk proses queue tiap 1 menit (max 1 job per run)
Schedule::command('queue:work --max-jobs=1 --stop-when-empty --sleep=0 --timeout=120')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule untuk bersihkan file temporary import yang lebih dari 24 jam
Schedule::call(function () {
    $dir = storage_path('app/import-temp');
    if (! is_dir($dir)) {
        return;
    }
    foreach (glob($dir.'/*.xlsx') as $file) {
        if (filemtime($file) < now()->subHours(24)->timestamp) {
            @unlink($file);
        }
    }
})->daily();
