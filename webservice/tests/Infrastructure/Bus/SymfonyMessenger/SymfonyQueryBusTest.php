<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Bus\SymfonyMessenger;

use App\Core\Contracts\Bus\Query;
use App\Infrastructure\Bus\SymfonyMessenger\SymfonyQueryBus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[CoversClass(SymfonyQueryBus::class)]
#[Group('unit')]
final class SymfonyQueryBusTest extends TestCase
{
    public function testGet(): void
    {
        $query = new class implements Query {};

        $envelope = (new Envelope($query))->with(new HandledStamp(['result'], 'SomeHandler'));
        $mockMessageBus = self::createMock(MessageBusInterface::class);
        $mockMessageBus->expects(self::once())
            ->method('dispatch')->with($query)
            ->willReturn($envelope);

        $sut = new SymfonyQueryBus($mockMessageBus);
        $result = $sut->get($query);

        self::assertSame(['result'], $result);
    }
}
