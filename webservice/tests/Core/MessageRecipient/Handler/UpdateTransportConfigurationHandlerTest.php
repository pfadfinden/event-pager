<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\UpdateTransportConfiguration;
use App\Core\MessageRecipient\Handler\UpdateTransportConfigurationHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(UpdateTransportConfigurationHandler::class)]
#[CoversClass(UpdateTransportConfiguration::class)]
final class UpdateTransportConfigurationHandlerTest extends TestCase
{
    public function testUpdateTransportConfiguration(): void
    {
        $recipientId = Ulid::generate();
        $transportKey = 'email';
        $vendorConfig = ['api_key' => 'updated123'];

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $transportKey,
            $vendorConfig,
            true,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with($vendorConfig);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationByKey')
            ->with($transportKey)
            ->willReturn($config);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())
            ->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id): bool => $id->equals(Ulid::fromString($recipientId))))
            ->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        $sut($command);
    }

    public function testUpdateTransportConfigurationDisableAndClearConfig(): void
    {
        $recipientId = Ulid::generate();
        $transportKey = 'sms';

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $transportKey,
            null,
            false,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with(null);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationByKey')
            ->with($transportKey)
            ->willReturn($config);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        $sut($command);
    }

    public function testUpdateTransportConfigurationRecipientNotFound(): void
    {
        $recipientId = Ulid::generate();

        $command = new UpdateTransportConfiguration(
            $recipientId,
            'email',
            null,
            true,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Recipient with ID {$recipientId} not found.");

        $sut($command);
    }

    public function testUpdateTransportConfigurationNotFound(): void
    {
        $recipientId = Ulid::generate();
        $transportKey = 'nonexistent';

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $transportKey,
            null,
            true,
        );

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationByKey')
            ->with($transportKey)
            ->willReturn(null);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Transport configuration for key '{$transportKey}' not found.");

        $sut($command);
    }

    public function testUpdateTransportConfigurationWithInvalidRecipientId(): void
    {
        $command = new UpdateTransportConfiguration(
            'invalid-id',
            'email',
            null,
            true,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::never())->method('getRecipientFromID');

        $uow = self::createStub(UnitOfWork::class);

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed recipient ID');

        $sut($command);
    }
}
