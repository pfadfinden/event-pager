<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Bus\SymfonyMessenger;

use App\Infrastructure\Bus\SymfonyMessenger\SymfonyCommandBus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(SymfonyCommandBus::class)]
#[Group('unit')]
final class SymfonyCommandBusTest extends TestCase
{
    public function testDo(): void
    {
        $cmd = new stdClass();

        $mockMessageBus = self::createMock(MessageBusInterface::class);
        $mockMessageBus->expects(self::once())->method('dispatch')->with($cmd)
            ->willReturn(new Envelope($cmd));

        $sut = new SymfonyCommandBus($mockMessageBus);
        $sut->do($cmd);
    }
}
