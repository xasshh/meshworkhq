<?php

namespace App\Exceptions;

use Exception;

final class BriefNotAvailableException extends Exception
{
    public function __construct(string $reason = 'This brief is no longer available for unlocking.')
    {
        parent::__construct($reason);
    }
}
