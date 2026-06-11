<?php

namespace App\Enums;

enum AlertStatus: string
{
    case Notified = 'notified';
    case Viewed = 'viewed';
    case Unlocked = 'unlocked';
    case Pitched = 'pitched';
    case Resolved = 'resolved';
}
