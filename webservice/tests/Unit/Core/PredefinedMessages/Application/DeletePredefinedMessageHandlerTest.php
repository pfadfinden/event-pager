<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\PredefinedMessages\Application;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Application\DeletePredefinedMessageHandler;
use App\Core\PredefinedMessages\Command\DeletePredefinedMessage;
use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(DeletePredefinedMessageHandler::class)]
#[CoversClass(DeletePredefinedMessage::class)]
#[AllowMockObjectsWithoutExpectations]
final class DeletePredefinedMessageHandlerTest extends TestCase
{
    public function testDeletePredefinedMessage(): void
    {
        $id = Ulid::generate();

        $command = new DeletePredefinedMessage($id);

        $message = $this->createMock(PredefinedMessage::class);

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Ulid $ulid): bool => $ulid->equals(Ulid::fromString($id))))
            ->willReturn($message);
        $repo->expects(self::once())->method('remove')->with($message);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new DeletePredefinedMessageHandler($repo, $uow);

        $sut($command);
    }

    public function testDeletePredefinedMessageNotFound(): void
    {
        $id = Ulid::generate();

        $command = new DeletePredefinedMessage($id);

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())->method('getById')->willReturn(null);
        $repo->expects(self::never())->method('remove');

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new DeletePredefinedMessageHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Predefined message with ID {$id} not found.");

        $sut($command);
    }

    public function testDeletePredefinedMessageWithInvalidId(): void
    {
        $command = new DeletePredefinedMessage('invalid-id');

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::never())->method('getById');
        $repo->expects(self::never())->method('remove');

        $uow = self::createStub(UnitOfWork::class);

        $sut = new DeletePredefinedMessageHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed predefined message ID');

        $sut($command);
    }
}
