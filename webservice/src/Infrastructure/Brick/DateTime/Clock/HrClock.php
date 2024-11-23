<?php

declare(strict_types=1);

namespace App\Infrastructure\Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Instant;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use function hrtime;

/**
 * This high resolution clock uses nanosecond precision.
 *
 * The library's default clock is backed by `microtime()`, not `hrtime()`.
 */
#[AsAlias]
final class HrClock implements Clock
{
    public function getTime(): Instant
    {
        return Instant::of(...hrtime());
    }
}
