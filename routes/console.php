<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('programs:start')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('social-publications:dispatch-pending')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('queue:work --queue=default --stop-when-empty --tries=4 --timeout=120')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

Schedule::command('audience:collect')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('audience:prune')
    ->cron('0 3 1 1,7 *')
    ->withoutOverlapping();

Schedule::command('posts:publish-reviewed --hours=24 --news-hours=2 --limit=25')
    ->hourly()
    ->withoutOverlapping();
