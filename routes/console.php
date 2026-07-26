<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Status transitions run first each day so the reminder commands right after
// see up-to-date Active/Expired/Closed states.
Schedule::command('app:sync-membership-statuses')->dailyAt('00:30');
Schedule::command('app:send-renewal-reminders')->dailyAt('09:00');
Schedule::command('app:send-closure-reminders')->dailyAt('09:15');

Schedule::command('app:auto-checkout-attendance')->dailyAt('22:05');
Schedule::command('zkteco:pull-attendance')->everyFiveMinutes();
