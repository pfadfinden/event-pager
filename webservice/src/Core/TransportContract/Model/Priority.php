<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

/*
 * Defines Priorities for the Messages
 */
enum Priority: int
{
    case URGENT = 50;
    case HIGH = 40;
    case DEFAULT = 30;
    case LOW = 20;
    case MIN = 10;
}
