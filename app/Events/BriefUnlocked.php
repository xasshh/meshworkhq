<?php

namespace App\Events;

use App\Models\Unlock;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Unlock $unlock,
    ) {}
}
