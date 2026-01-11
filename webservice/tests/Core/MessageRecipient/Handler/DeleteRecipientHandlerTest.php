<?php

declare(strict_types=1);

namespace App\Tests\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\DeleteRecipient;
use App\Core\MessageRecipient\Handler\DeleteRecipientHandler;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(DeleteRecipientHandler::class)]
#[CoversClass(DeleteRecipient::class)]
final class DeleteRecipientHandlerTest extends TestCase
{
    public function testRemovePerson(): void
    {
        $idToDelete = Ulid::generate();
        // Arrange
        $command = new DeleteRecipient(
            $idToDelete
        );

        $person = self::createStub(Person::class);
        $person->method('getRoles')->willReturn([]);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id) => $id->equals(Ulid::fromString($idToDelete))))
            ->willReturn($person);
        $repo->expects(self::once())->method('remove')->with($person);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new DeleteRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testRemovePersonNull(): void
    {
        self::expectException(InvalidArgumentException::class);
        $idToDelete = Ulid::generate();
        // Arrange
        $command = new DeleteRecipient(
            $idToDelete
        );

        $person = self::createStub(Person::class);
        $person->method('getRoles')->willReturn([]);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new DeleteRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testRemovePersonWithAssignedRoles(): void
    {
        self::expectException(InvalidArgumentException::class);
        $idToDelete = Ulid::generate();
        // Arrange
        $command = new DeleteRecipient(
            $idToDelete
        );

        $person = self::createStub(Person::class);
        $person->method('getRoles')->willReturn(['Role1', 'Role2']);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')
            ->with(self::callback(fn (Ulid $id) => $id->equals(Ulid::fromString($idToDelete))))
            ->willReturn($person);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new DeleteRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }
}
