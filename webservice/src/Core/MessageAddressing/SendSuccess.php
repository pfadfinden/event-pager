<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

enum SendSuccess: int
{
    case SENT = 1;
    case NOT_SENT = 0;
    case ERROR = -1;
}
