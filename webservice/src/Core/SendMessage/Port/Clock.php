<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use Brick\DateTime\Instant;

/**
 * Port for getting the current time during message addressing.
 * Allows for easier testing by injecting a controllable clock.
 */
interface Clock
{
    public function now(): Instant;
}
