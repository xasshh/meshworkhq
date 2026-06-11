<?php

namespace App\Listeners;

use App\Events\BriefPublished;
use App\Services\AlertService;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchBriefToAlerts implements ShouldQueue
{
    public string $queue = 'alerts';

    public function __construct(
        private readonly AlertService $alertService,
    ) {}

    public function handle(BriefPublished $event): void
    {
        $this->alertService->initiate($event->brief);
    }
}
