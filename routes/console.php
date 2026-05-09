<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| DevOps: Automated Database Backup Schedule
|--------------------------------------------------------------------------
|
| Runs the backup:database command daily at 2:00 AM.
| The --cleanup flag automatically removes backups older than 7 days.
|
| To test manually: php artisan backup:database --cleanup
| To change schedule: modify the time below
|
*/
Schedule::command('backup:database --cleanup')->dailyAt('02:00');

