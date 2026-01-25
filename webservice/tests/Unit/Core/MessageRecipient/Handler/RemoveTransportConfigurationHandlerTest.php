<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\RemoveTransportConfiguration;
use App\Core\MessageRecipient\Handler\RemoveTransportConfigurationHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RemoveTransportConfigurationHandler::class)]
#[CoversClass(RemoveTransportConfiguration::class)]
final class RemoveTransportConfigurationHandlerTest extends TestCase
{
    public function testRemoveTransportConfiguration(): void
    {
        $recipientId = Ulid::generate();
        $configId = Ulid::generate();

        $command = new RemoveTransportConfiguration(
            $recipientId,
            $configId,
        );

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('removeTransportConfigurationById')
            ->with($configId);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())
            ->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id): bool => $id->equals(Ulid::fromString($recipientId))))
            ->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new RemoveTransportConfigurationHandler($repo, $uow);

        $sut($command);
    }

    public function testRemoveTransportConfigurationRecipientNotFound(): void
    {
        $recipientId = Ulid::generate();
        $configId = Ulid::generate();

        $command = new RemoveTransportConfiguration(
            $recipientId,
            $configId,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new RemoveTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Recipient with ID {$recipientId} not found.");

        $sut($command);
    }

    public function testRemoveTransportConfigurationWithInvalidRecipientId(): void
    {
        $configId = Ulid::generate();

        $command = new RemoveTransportConfiguration(
            'invalid-id',
            $configId,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::never())->method('getRecipientFromID');

        $uow = self::createStub(UnitOfWork::class);

        $sut = new RemoveTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed recipient ID');

        $sut($command);
    }
}
