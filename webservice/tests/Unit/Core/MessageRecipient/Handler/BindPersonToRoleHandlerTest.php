<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageRecipient\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\BindPersonToRole;
use App\Core\MessageRecipient\Handler\BindPersonToRoleHandler;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(BindPersonToRoleHandler::class)]
#[CoversClass(BindPersonToRole::class)]
final class BindPersonToRoleHandlerTest extends TestCase
{
    public function testCanBindPerson(): void
    {
        // Arrange
        $roleID = Ulid::generate();
        $personID = Ulid::generate();

        $command = new BindPersonToRole(
            $roleID,
            $personID,
        );

        $person = self::createStub(Person::class);
        $role = $this->createMock(Role::class);
        $role->expects(self::once())->method('bindPerson')->with($person);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::exactly(2))->method('getRecipientFromID')->willReturnOnConsecutiveCalls($role, $person);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new BindPersonToRoleHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testCanUnbindPerson(): void
    {
        // Arrange
        $roleID = Ulid::generate();
        $personID = null;

        $command = new BindPersonToRole(
            $roleID,
            $personID,
        );

        $person = null;
        $role = $this->createMock(Role::class);
        $role->expects(self::once())->method('bindPerson')->with($person);

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturnOnConsecutiveCalls($role, $person);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new BindPersonToRoleHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testErrorOnNoInvalidPersonID(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Malformed person ID');
        // Arrange
        $roleID = Ulid::generate();
        $personID = 'test';

        $command = new BindPersonToRole(
            $roleID,
            $personID,
        );

        $role = self::createStub(Role::class);
        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn($role);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new BindPersonToRoleHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testErrorOnNoRole(): void
    {
        self::expectException(InvalidArgumentException::class);
        // Arrange
        $roleID = Ulid::generate();
        $personID = Ulid::generate();

        $command = new BindPersonToRole(
            $roleID,
            $personID,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $repo->expects(self::once())->method('getRecipientFromID')->willReturn(null);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new BindPersonToRoleHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }

    public function testErrorOnNoPerson(): void
    {
        self::expectException(InvalidArgumentException::class);
        // Arrange
        $roleID = Ulid::generate();
        $personID = Ulid::generate();

        $command = new BindPersonToRole(
            $roleID,
            $personID,
        );

        $repo = $this->createMock(MessageRecipientRepository::class);
        $role = self::createStub(Role::class);
        $repo->expects(self::exactly(2))->method('getRecipientFromID')->willReturnOnConsecutiveCalls($role, null);

        $uow = self::createStub(UnitOfWork::class);

        $sut = new BindPersonToRoleHandler($repo, $uow);

        // Act
        $sut($command);

        // Assert = see expectations
    }
}
