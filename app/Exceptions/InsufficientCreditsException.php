<?php

namespace App\Exceptions;

use Exception;

final class InsufficientCreditsException extends Exception
{
    public function __construct(
        public readonly int $required,
        public readonly int $available,
    ) {
        parent::__construct(
            "Insufficient credits: {$required} required, {$available} available."
        );
    }
}
