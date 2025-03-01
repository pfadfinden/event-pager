<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\ReplaceName;
use App\Core\MessageRecipient\Handler\ReplaceNameHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(ReplaceNameHandler::class)]
#[CoversClass(ReplaceName::class)]
final class ReplaceNameHandlerTest extends TestCase
{
    public function testReplaceNameOnAbstractRecipient(): void
    {
        $recipientID = Ulid::generate();
        $name = 'instance a';
        // Arrange
        $command = new ReplaceName(
            $recipientID,
            $name,
        );

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($recipient);
        $recipient->expects(self::once())->method('setName');

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new ReplaceNameHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testReplaceNameOnAbstractRecipientErrorOnRecipient(): void
    {
        self::expectException(InvalidArgumentException::class);
        $recipientID = Ulid::generate();
        $name = 'instance a';
        // Arrange
        $command = new ReplaceName(
            $recipientID,
            $name,
        );

        $recipient = $this->createMock(AbstractMessageRecipient::class);
        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = $this->createMock(UnitOfWork::class);

        $sut = new ReplaceNameHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }
}
