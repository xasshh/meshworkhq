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
