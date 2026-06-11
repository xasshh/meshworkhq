<?php

namespace App\Jobs;

use App\Models\Brief;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class DispatchAlertWaveJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  Collection<int, User>  $professionals
     */
    public function __construct(
        private readonly Brief $brief,
        private readonly Collection $professionals,
        private readonly int $wave,
    ) {}

    public function handle(AlertService $alertService): void
    {
        $alertService->dispatchWave($this->brief, $this->professionals, $this->wave);
    }
}
