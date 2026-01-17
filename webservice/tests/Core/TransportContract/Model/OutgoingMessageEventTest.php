<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportContract\Model;

use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(OutgoingMessageEvent::class)]
#[Group('unit')]
final class OutgoingMessageEventTest extends TestCase
{
    public function testFailedToQueue(): void
    {
        $sut = OutgoingMessageEvent::failedToQueue(Ulid::fromString(Ulid::generate()));

        self::assertSame(OutgoingMessageStatus::ERROR, $sut->status);
    }

    public function testQueued(): void
    {
        $sut = OutgoingMessageEvent::queued(Ulid::fromString(Ulid::generate()));

        self::assertSame(OutgoingMessageStatus::QUEUED, $sut->status);
    }

    public function testTransmitted(): void
    {
        $sut = OutgoingMessageEvent::transmitted(Ulid::fromString(Ulid::generate()));

        self::assertSame(OutgoingMessageStatus::TRANSMITTED, $sut->status);
    }
}
