<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Brick\DateTime\Clock;

use App\Infrastructure\Brick\DateTime\Clock\HrClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use function hrtime;

#[CoversClass(HrClock::class)]
#[Small()]
final class HrClockTest extends TestCase
{
    public function testGetTime(): void
    {
        $clock = new HrClock();

        $before = hrtime(true);
        $now = $clock->getTime();
        $after = hrtime(true);

        $nanoseconds = $now->getEpochSecond() * 1_000_000_000 + $now->getNano();

        self::assertGreaterThanOrEqual($before, $nanoseconds);
        self::assertLessThanOrEqual($after, $nanoseconds);
    }
}
