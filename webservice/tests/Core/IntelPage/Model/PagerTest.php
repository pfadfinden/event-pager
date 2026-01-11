<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Model\IndividualCapAssignment;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(Pager::class)]
#[Group('unit')]
final class PagerTest extends TestCase
{
    public function testCapAssignment(): void
    {
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 1', 3);
        $channel = new Channel(Ulid::fromString(Ulid::generate()), 'Channel 1', CapCode::fromInt(2));

        $pager->clearSlot(Slot::fromInt(0))
            ->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(1), false, false)
            ->assignChannel(Slot::fromInt(7), $channel);

        $assignments = iterator_to_array($pager->getCapAssignments());
        self::assertArrayNotHasKey(0, $assignments);
        self::assertInstanceOf(IndividualCapAssignment::class, $assignments[1]);
        self::assertInstanceOf(ChannelCapAssignment::class, $assignments[7]);
    }

    public function testClearCapAssignment(): void
    {
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 1', 3);
        $channel = new Channel(Ulid::fromString(Ulid::generate()), 'Channel 1', CapCode::fromInt(2));

        $pager
            ->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(1), false, false)
            ->assignChannel(Slot::fromInt(7), $channel)
            ->clearSlot(Slot::fromInt(0));

        $assignments = iterator_to_array($pager->getCapAssignments());
        self::assertArrayNotHasKey(0, $assignments);
        self::assertInstanceOf(IndividualCapAssignment::class, $assignments[1]);
        self::assertInstanceOf(ChannelCapAssignment::class, $assignments[7]);
    }

    public function testSlotOutOfBoundsUpper(): void
    {
        self::expectException(InvalidArgumentException::class);

        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 2', 2);
        $pager->clearSlot(Slot::fromInt(8));
    }

    public function testSlotOutOfBoundsLower(): void
    {
        self::expectException(InvalidArgumentException::class);

        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 3', 3);
        $pager->clearSlot(Slot::fromInt(-1));
    }

    public function testIndividualAlertCap(): void
    {
        // Arrange
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 1', 3);
        $channel = new Channel(Ulid::fromString(Ulid::generate()), 'Channel 1', CapCode::fromInt(2));

        self::assertNull($pager->individualAlertCap(), 'Gracefully returns null when no slots configured');

        $pager
            ->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(1), false, false)
            ->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(2), false, true)
            ->assignChannel(Slot::fromInt(2), $channel)
            ->assignIndividualCap(Slot::fromInt(3), CapCode::fromInt(3), true, false)
            ->assignChannel(Slot::fromInt(4), $channel)
            ->assignIndividualCap(Slot::fromInt(5), CapCode::fromInt(4), true, true)
            ->assignChannel(Slot::fromInt(6), $channel)
            ->assignChannel(Slot::fromInt(7), $channel);

        // Act & Assert
        // @phpstan-ignore method.nonObject (no idea why phpstan shows this error)
        self::assertEquals(3, $pager->individualAlertCap()?->getCode());
    }

    public function testIndividualNonAlertCap(): void
    {
        // Arrange
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 1', 3);
        $channel = new Channel(Ulid::fromString(Ulid::generate()), 'Channel 1', CapCode::fromInt(2));

        self::assertNull($pager->individualNonAlertCap(), 'Gracefully returns null when no slots configured');

        $pager
            ->assignIndividualCap(Slot::fromInt(0), CapCode::fromInt(1), true, false)
            ->assignIndividualCap(Slot::fromInt(1), CapCode::fromInt(2), true, true)
            ->assignChannel(Slot::fromInt(2), $channel)
            ->assignIndividualCap(Slot::fromInt(3), CapCode::fromInt(3), false, false)
            ->assignChannel(Slot::fromInt(4), $channel)
            ->assignIndividualCap(Slot::fromInt(5), CapCode::fromInt(4), false, true)
            ->assignChannel(Slot::fromInt(6), $channel)
            ->assignChannel(Slot::fromInt(7), $channel);

        // Act & Assert
        // @phpstan-ignore method.nonObject (no idea why phpstan shows this error)
        self::assertEquals(3, $pager->individualNonAlertCap()?->getCode());
    }

    public function testIsActive(): void
    {
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 3', 3);

        self::assertFalse($pager->isActivated(), 'Pager should be deactivated by default');
        $pager->setActivated(true);
        self::assertTrue($pager->isActivated(), 'Pager should be activated after activating it');
        $pager->setActivated(false);
        self::assertFalse($pager->isActivated(), 'Pager should be deactivated after deactivating it');
    }

    public function testCarriedBy(): void
    {
        $pager = new Pager(Ulid::fromString(Ulid::generate()), 'Pager 3', 3);
        $recipient = self::createStub(AbstractMessageRecipient::class);

        self::assertNull($pager->getCarriedBy(), 'Pager is not carried by anyone when new');
        $pager->setCarriedBy($recipient);
        self::assertEquals($recipient, $pager->getCarriedBy(), 'Pager should be activated after activating it');
        $pager->setCarriedBy(null);
        self::assertNull($pager->getCarriedBy(), 'Pager not carried by after it was returned');
    }
}
