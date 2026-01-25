<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\CreateRecipient;
use App\Core\MessageRecipient\Handler\CreateRecipientHandler;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[\PHPUnit\Framework\Attributes\Group('unit')]
#[CoversClass(CreateRecipientHandler::class)]
#[CoversClass(CreateRecipient::class)]
final class CreateRecipientHandlerTest extends TestCase
{
    public function testCreateNewRole(): void
    {
        // Arrange
        $command = new CreateRecipient(
            Ulid::generate(),
            'role',
            'Role A',
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('add')->with(self::logicalAnd(
            self::isInstanceOf(Role::class),
            self::callback(fn (Role $role): bool => 'Role A' === $role->getName())
        ));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new CreateRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testCreateNewPerson(): void
    {
        // Arrange
        $command = new CreateRecipient(
            Ulid::generate(),
            'person',
            'Peter',
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('add')->with(self::logicalAnd(
            self::isInstanceOf(Person::class),
            self::callback(fn (Person $person): bool => 'Peter' === $person->getName())
        ));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new CreateRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testCreateNewGroup(): void
    {
        // Arrange
        $command = new CreateRecipient(
            Ulid::generate(),
            'group',
            'Group 1',
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('add')->with(self::logicalAnd(
            self::isInstanceOf(Group::class),
            self::callback(fn (Group $group): bool => 'Group 1' === $group->getName())
        ));

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new CreateRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testCreateInvalidRecipient(): void
    {
        self::expectException(InvalidArgumentException::class);
        // Arrange
        $command = new CreateRecipient(
            Ulid::generate(),
            'test',
            'testRecepient',
        );

        $repo = self::createStub(MessageRecipientRepository::class);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new CreateRecipientHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }
}
