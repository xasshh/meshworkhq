<?php

namespace App\Enums;

enum UnlockStatus: string
{
    case Active = 'active';
    case Pitched = 'pitched';
    case Completed = 'completed';
    case Refunded = 'refunded';
}
