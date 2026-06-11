<?php

namespace App\Jobs;

use App\Services\BriefService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExpireOverdueBriefsJob implements ShouldQueue
{
    use Queueable;

    public function handle(BriefService $briefService): void
    {
        $count = $briefService->expireOverdue();

        if ($count > 0) {
            Log::info("Expired {$count} overdue brief(s).");
        }
    }
}
