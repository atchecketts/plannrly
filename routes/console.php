<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run shift status updates every minute
Schedule::command('shifts:update-statuses')->everyMinute();

// Detect missed shifts and create time entries every 15 minutes
Schedule::command('attendance:detect-missed-shifts')->everyFifteenMinutes();

// Send shift reminders (day-before and hour-before)
Schedule::command('shifts:send-reminders')->hourly();

// Extend recurring shifts daily (creates future instances)
Schedule::command('shifts:extend-recurring')->daily();
