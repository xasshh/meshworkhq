<?php

use App\Jobs\ExpireOverdueBriefsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire overdue briefs every hour.
Schedule::job(new ExpireOverdueBriefsJob)->hourly();

// Drain the queue from the scheduler as well.
//
// On a host running a supervised worker this finds nothing and exits within
// the second. On shared hosting, where a long lived process is not available
// and a per minute cron is all there is, it is the only thing that delivers
// alerts and email at all. The short lock expiry means a run killed mid flight
// blocks the next one for two minutes rather than a day.
Schedule::command('queue:work --queue=alerts,default --stop-when-empty --max-time=55')
    ->everyMinute()
    ->withoutOverlapping(2);
