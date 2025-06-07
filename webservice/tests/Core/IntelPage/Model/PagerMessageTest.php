<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(PagerMessage::class)]
#[Group('unit')]
final class PagerMessageTest extends TestCase
{
    public function testCreate(): PagerMessage
    {
        $id = Ulid::fromString(Ulid::generate());
        $message = PagerMessage::new($id, 'default', CapCode::fromInt(1001), 'Hello World', 1);

        self::assertEquals('Hello World', $message->getMessage());
        self::assertEquals(1001, $message->getCap()->getCode());
        self::assertTrue($id->equals($message->getId()));

        return $message;
    }

    #[Depends('testCreate')]
    public function testCanBeMarkedAsTransmitted(PagerMessage $message): void
    {
        // Arrange
        $targetTimestamp = Instant::of(1_800_000_000);
        DefaultClock::set(new FixedClock($targetTimestamp));

        // Assert 1
        self::assertNull(
            $message->getTransmittedOn(),
            'A message that was not transmitted should not have a transmission timestamp'
        );

        // Act
        $message->markSend();

        // Assert 2
        self::assertTrue(
            $message->getTransmittedOn() instanceof Instant && $targetTimestamp->isEqualTo($message->getTransmittedOn()),
            'A message that was transmitted should have a transmission timestamp'
        );
    }

    #[Depends('testCreate')]
    public function testMarkingAsFailedIncreasesFailureCount(PagerMessage $message): void
    {
        self::assertEquals(0, $message->getAttemptedToSend());
        $message->failedToSend();
        self::assertEquals(1, $message->getAttemptedToSend());
    }
}
