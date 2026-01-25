<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\BindRecipientToGroup;
use App\Core\MessageRecipient\Handler\BindRecipientToGroupHandler;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[\PHPUnit\Framework\Attributes\Group('unit')]
#[CoversClass(BindRecipientToGroupHandler::class)]
#[CoversClass(BindRecipientToGroup::class)]
final class BindRecipientToGroupHandlerTest extends TestCase
{
    public function testAddRecipientToGroup(): void
    {
        // Arrange
        $recipientID = Ulid::generate();
        $groupID = Ulid::generate();

        $command = new BindRecipientToGroup(
            $groupID,
            $recipientID,
        );

        $recipient = self::createStub(AbstractMessageRecipient::class);
        $group = $this->createMock(Group::class);
        $group->expects(self::once())->method('addMember')->with($recipient);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::exactly(2))->method('getRecipientFromID')->willReturnOnConsecutiveCalls($group, $recipient);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new BindRecipientToGroupHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testAddRecipientToGroupErrorOnGroup(): void
    {
        self::expectException(InvalidArgumentException::class);
        // Arrange
        $recipientID = Ulid::generate();
        $groupID = Ulid::generate();

        self::expectExceptionMessage("Group with ID {$groupID} not found.");

        $command = new BindRecipientToGroup(
            $groupID,
            $recipientID,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new BindRecipientToGroupHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testAddRecipientToGroupErrorOnRecipient(): void
    {
        self::expectException(InvalidArgumentException::class);
        // Arrange
        $recipientID = Ulid::generate();
        $groupID = Ulid::generate();

        self::expectExceptionMessage("Recipient with ID {$recipientID} not found.");

        $command = new BindRecipientToGroup(
            $groupID,
            $recipientID,
        );

        $group = self::createStub(Group::class);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::exactly(2))->method('getRecipientFromID')->willReturnOnConsecutiveCalls($group, null);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new BindRecipientToGroupHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }
}
