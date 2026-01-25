<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\AddTransportConfiguration;
use App\Core\MessageRecipient\Handler\AddTransportConfigurationHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(AddTransportConfigurationHandler::class)]
#[CoversClass(AddTransportConfiguration::class)]
final class AddTransportConfigurationHandlerTest extends TestCase
{
    public function testAddTransportConfiguration(): void
    {
        $recipientId = Ulid::generate();
        $transportKey = 'email';
        $vendorConfig = ['api_key' => 'test123'];

        $command = new AddTransportConfiguration(
            $recipientId,
            $transportKey,
            $vendorConfig,
            true,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with($vendorConfig);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('addTransportConfiguration')
            ->with($transportKey)
            ->willReturn($config);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())
            ->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id): bool => $id->equals(Ulid::fromString($recipientId))))
            ->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddTransportConfigurationHandler($repo, $uow);

        $sut($command);
    }

    public function testAddTransportConfigurationDisabled(): void
    {
        $recipientId = Ulid::generate();
        $transportKey = 'sms';

        $command = new AddTransportConfiguration(
            $recipientId,
            $transportKey,
            null,
            false,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with(null);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('addTransportConfiguration')
            ->with($transportKey)
            ->willReturn($config);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddTransportConfigurationHandler($repo, $uow);

        $sut($command);
    }

    public function testAddTransportConfigurationRecipientNotFound(): void
    {
        $recipientId = Ulid::generate();

        $command = new AddTransportConfiguration(
            $recipientId,
            'email',
            null,
            true,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new AddTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Recipient with ID {$recipientId} not found.");

        $sut($command);
    }

    public function testAddTransportConfigurationWithInvalidRecipientId(): void
    {
        $command = new AddTransportConfiguration(
            'invalid-id',
            'email',
            null,
            true,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::never())->method('getRecipientFromID');

        $uow = self::createStub(UnitOfWork::class);

        $sut = new AddTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed recipient ID');

        $sut($command);
    }
}
