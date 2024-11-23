<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\SendMessage\Command\SendMessage;
use App\Core\SendMessage\Handler\SendMessageHandler;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class SendMessageHandlerTest extends TestCase
{
    public function testProcessNew(): void
    {
        // Arrange
        DefaultClock::set(new FixedClock(Instant::of(1_000_000_000)));

        $command = new SendMessage(
            'Hello World',
            Ulid::generate(),
            1,
            [Ulid::generate(), Ulid::generate()]
        );

        $repo = $this->createMock(IncomingMessageRepository::class);
        $repo->expects(self::once())->method('add')->with(self::isInstanceOf(IncomingMessage::class));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new SendMessageHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations

        // Cleanup
        DefaultClock::reset();
    }
}
