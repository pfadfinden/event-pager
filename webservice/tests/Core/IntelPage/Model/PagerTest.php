<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Model;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Model\IndividualCapAssignment;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

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
}
