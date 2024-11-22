<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

enum Priority: int
{
    case LOW = 0;
    case NORMAL = 1;
    case HIGH = 2;
    case ALERT = 3;
}
