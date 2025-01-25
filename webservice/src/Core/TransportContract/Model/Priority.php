<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

/*
 * Priorities that messages can be sent with
 */
enum Priority: int
{
    case URGENT = 50;
    case HIGH = 40;
    case DEFAULT = 30;
    case LOW = 20;
    case MIN = 10;

    public function isHigherOrEqual(Priority $other): bool
    {
        return $this->value >= $other->value;
    }
}
