<?php

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\PagerMessage;
use Brick\DateTime\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class PagerMessageTest extends TestCase
{

    public function testCreate(): PagerMessage {
        $id = Ulid::fromString(Ulid::generate());
        $message = PagerMessage::new($id, CapCode::fromInt(1001), 'Hello World',1);

        self::assertInstanceOf(PagerMessage::class, $message);
        self::assertEquals("Hello World", $message->getMessage());
        self::assertEquals(1001, $message->getCap()->getCode());
        self::assertTrue($id->equals($message->getId()));

        return $message;
    }

    #[Depends('testCreate')]
    public function testCanBeMarkedAsTransmitted(PagerMessage $message): void {
        $targetTimestamp = Instant::of(1_800_000_000);
        DefaultClock::set(new FixedClock($targetTimestamp));

        self::assertNull($message->getTransmittedOn(), 'A message that was not transmitted should not have a transmission timestamp');
        $message->markSend();
        self::assertTrue($targetTimestamp->isEqualTo($message->getTransmittedOn()), 'A message that was transmitted should have a transmission timestamp');
    }

    #[Depends('testCreate')]
    public function testMarkingAsFailedIncreasesFailureCount(PagerMessage $message): void {
        self::assertEquals(0, $message->getAttemptedToSend());
        $message->failedToSend();
        self::assertEquals(1, $message->getAttemptedToSend());
    }
}
