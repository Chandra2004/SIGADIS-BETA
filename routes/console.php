<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Flows.md §7.3.2: cek tiap menit, jalankan `php artisan schedule:work` di server.
Schedule::command('alerts:escalate')->everyMinute();

// Flows.md §19.3.3: tidak perlu real-time, cukup berkala.
Schedule::command('privacy:process-deletions')->hourly();

// Flows.md §34.3.4: cek tiap hari, cukup untuk unavailable_until berbasis tanggal.
Schedule::command('staff:reactivate-expired-availability')->daily();

// Flows.md §6.3: cek tiap hari, cukup untuk pengingat berbasis tanggal jatuh tempo.
Schedule::command('screening:send-reminders')->daily();
