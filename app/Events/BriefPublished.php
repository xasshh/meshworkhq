<?php

namespace App\Events;

use App\Models\Brief;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Brief $brief,
    ) {}
}
