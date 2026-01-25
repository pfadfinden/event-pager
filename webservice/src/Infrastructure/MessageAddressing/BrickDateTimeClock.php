<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageAddressing;

use App\Core\SendMessage\Port\Clock;
use Brick\DateTime\Clock as BrickClock;
use Brick\DateTime\Instant;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias]
final readonly class BrickDateTimeClock implements Clock
{
    public function __construct(
        private BrickClock $clock,
    ) {
    }

    public function now(): Instant
    {
        return $this->clock->getTime();
    }
}
