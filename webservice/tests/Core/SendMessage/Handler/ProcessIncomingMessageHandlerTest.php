<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Handler;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Handler\ProcessIncomingMessageHandler;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use App\Core\TransportContract\Port\TransportManager;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(ProcessIncomingMessage::class)]
#[CoversClass(ProcessIncomingMessageHandler::class)]
final class ProcessIncomingMessageHandlerTest extends TestCase
{
    public function testProcessNew(): void
    {
        // Arrange
        $id = Ulid::generate();
        $command = new ProcessIncomingMessage($id);

        $repoM = $this->createMock(IncomingMessageRepository::class);
        $repoM->expects(self::once())->method('getWithId')->willReturn(
            new IncomingMessage(
                Ulid::fromString($id), Instant::now(), Ulid::fromString(Ulid::generate()), [
                    Ulid::fromString(Ulid::generate()),
                    Ulid::fromString(Ulid::generate()),
                ], 'Hello World!', 4
            )
        );

        $repoR = $this->createMock(MessageRecipientRepository::class);
        $mockRecipient = self::createStub(AbstractMessageRecipient::class);
        $repoR->expects(self::atLeast(2))->method('getRecipientFromID')->willReturn($mockRecipient);

        $transportManager = $this->createMock(TransportManager::class);

        // This Test *does not* test the message adressing part of the handler since it is preliminary
        $transportManager->expects(self::atLeast(1))->method('activeTransports')->willReturn([]);

        $sut = new ProcessIncomingMessageHandler($repoM, $repoR, $transportManager);

        // Act
        $sut($command);

        // Assert = see expectations

        // Cleanup
        DefaultClock::reset();
    }
}
