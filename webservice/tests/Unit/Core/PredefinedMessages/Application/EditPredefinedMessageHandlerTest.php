<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\PredefinedMessages\Application;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\PredefinedMessages\Application\EditPredefinedMessageHandler;
use App\Core\PredefinedMessages\Command\EditPredefinedMessage;
use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(EditPredefinedMessageHandler::class)]
#[CoversClass(EditPredefinedMessage::class)]
final class EditPredefinedMessageHandlerTest extends TestCase
{
    public function testEditPredefinedMessage(): void
    {
        $id = Ulid::generate();

        $command = new EditPredefinedMessage(
            $id,
            'Updated Title',
            'Updated content',
            4,
            ['01JNAY9HWQTEX1T45VBM2HG1XJ', '01JNAY9HWQTEX1T45VBM2HG1XK'],
            true,
            2,
            false,
        );

        $message = $this->createMock(PredefinedMessage::class);
        $message->expects(self::once())->method('setTitle')->with('Updated Title');
        $message->expects(self::once())->method('setMessageContent')->with('Updated content');
        $message->expects(self::once())->method('setPriority')->with(4);
        $message->expects(self::once())->method('setRecipientIds')->with(['01JNAY9HWQTEX1T45VBM2HG1XJ', '01JNAY9HWQTEX1T45VBM2HG1XK']);
        $message->expects(self::once())->method('setIsFavorite')->with(true);
        $message->expects(self::once())->method('setSortOrder')->with(2);
        $message->expects(self::once())->method('setIsEnabled')->with(false);

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Ulid $ulid): bool => $ulid->equals(Ulid::fromString($id))))
            ->willReturn($message);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new EditPredefinedMessageHandler($repo, $uow);

        $sut($command);
    }

    public function testEditPredefinedMessageNotFound(): void
    {
        $id = Ulid::generate();

        $command = new EditPredefinedMessage(
            $id,
            'Updated Title',
            'Updated content',
            4,
            [],
            true,
            2,
            true,
        );

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::once())->method('getById')->willReturn(null);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('commit');

        $sut = new EditPredefinedMessageHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Predefined message with ID {$id} not found.");

        $sut($command);
    }

    public function testEditPredefinedMessageWithInvalidId(): void
    {
        $command = new EditPredefinedMessage(
            'invalid-id',
            'Updated Title',
            'Updated content',
            4,
            [],
            true,
            2,
            true,
        );

        $repo = $this->createMock(PredefinedMessageRepository::class);
        $repo->expects(self::never())->method('getById');

        $uow = self::createStub(UnitOfWork::class);

        $sut = new EditPredefinedMessageHandler($repo, $uow);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed predefined message ID');

        $sut($command);
    }
}
