<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Model;

use App\Core\SendMessage\Model\IncomingMessage;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(IncomingMessage::class)]
final class IncomingMessageTest extends TestCase
{
    public function testCreateNew(): void
    {
        // Arrange
        DefaultClock::set(new FixedClock(Instant::of(1_000_000_000)));

        // Act
        $sut = IncomingMessage::new(
            Ulid::fromString(Ulid::generate()),
            [Ulid::fromString(Ulid::generate()), Ulid::fromString(Ulid::generate())],
            'Hello World',
            1
        );

        // Assert
        self::assertSame('Hello World', $sut->content);
        self::assertSame(1, $sut->priority);
        self::assertTrue($sut->sentOn->isEqualTo(Instant::of(1_000_000_000)));

        // Cleanup
        DefaultClock::reset();
    }
}
