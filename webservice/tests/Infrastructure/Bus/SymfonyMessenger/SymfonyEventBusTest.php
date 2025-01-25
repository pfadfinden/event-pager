<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Bus\SymfonyMessenger;

use App\Infrastructure\Bus\SymfonyMessenger\SymfonyEventBus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(SymfonyEventBus::class)]
#[Group('unit')]
final class SymfonyEventBusTest extends TestCase
{
    public function testPublish(): void
    {
        $event = new stdClass();

        $mockMessageBus = self::createMock(MessageBusInterface::class);
        $mockMessageBus->expects(self::once())->method('dispatch')->with($event)
            ->willReturn(new Envelope($event));

        $sut = new SymfonyEventBus($mockMessageBus);
        $sut->publish($event);
    }
}
