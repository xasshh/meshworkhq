<?php

namespace App\Exceptions;

use Exception;

final class AlreadyUnlockedException extends Exception
{
    public function __construct()
    {
        parent::__construct('You have already unlocked this brief.');
    }
}
