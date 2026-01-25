<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\UpdateTransportConfiguration;
use App\Core\MessageRecipient\Handler\UpdateTransportConfigurationHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(UpdateTransportConfigurationHandler::class)]
#[CoversClass(UpdateTransportConfiguration::class)]
final class UpdateTransportConfigurationHandlerTest extends TestCase
{
    public function testUpdateTransportConfiguration(): void
    {
        $recipientId = Ulid::generate();
        $configId = Ulid::generate();
        $vendorConfig = ['api_key' => 'updated123'];

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $configId,
            $vendorConfig,
            true,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with($vendorConfig);
        $config->expects(self::once())->method('setSelectionExpression')->with('true');
        $config->expects(self::once())->method('setContinueInHierarchy')->with(null);
        $config->expects(self::once())->method('setEvaluateOtherTransportConfigurations')->with(true);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationById')
            ->with(self::callback(fn (mixed $param) => $param instanceof Ulid && $param->equals(Ulid::fromString($configId))))
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
        $configId = Ulid::generate();

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $configId,
            null,
            false,
        );

        $config = $this->createMock(RecipientTransportConfiguration::class);
        $config->expects(self::once())->method('setVendorSpecificConfig')->with(null);
        $config->expects(self::once())->method('setSelectionExpression')->with('true');
        $config->expects(self::once())->method('setContinueInHierarchy')->with(null);
        $config->expects(self::once())->method('setEvaluateOtherTransportConfigurations')->with(true);

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationById')
            ->with(self::callback(fn (mixed $param) => $param instanceof Ulid && $param->equals(Ulid::fromString($configId))))
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
        $configId = Ulid::generate();

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $configId,
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
        $configId = Ulid::generate();

        $command = new UpdateTransportConfiguration(
            $recipientId,
            $configId,
            null,
            true,
        );

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $recipient->expects(self::once())
            ->method('getTransportConfigurationById')
            ->with(self::callback(fn (mixed $param) => $param instanceof Ulid && $param->equals(Ulid::fromString($configId))))
            ->willReturn(null);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new UpdateTransportConfigurationHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Transport configuration with ID '{$configId}' not found.");

        $sut($command);
    }

    public function testUpdateTransportConfigurationWithInvalidRecipientId(): void
    {
        $configId = Ulid::generate();

        $command = new UpdateTransportConfiguration(
            'invalid-id',
            $configId,
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
