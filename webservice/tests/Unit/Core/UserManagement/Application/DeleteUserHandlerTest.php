<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Application;

use App\Core\UserManagement\Application\DeleteUserHandler;
use App\Core\UserManagement\Command\DeleteUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteUser::class)]
#[CoversClass(DeleteUserHandler::class)]
#[Group('unit')]
final class DeleteUserHandlerTest extends TestCase
{
    public function testDeleteUserCommand(): void
    {
        $repository = self::createMock(UserRepository::class);
        $user = new User('test-user');

        $repository->expects(self::once())->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())->method('delete')
            ->with($user);

        $sut = new DeleteUserHandler($repository);
        $command = DeleteUser::with(
            'test-user'
        );
        $sut->__invoke($command);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $repository = self::createMock(UserRepository::class);

        $repository->expects(self::once())->method('findOneByUsername')
            ->with('nonexistent-user')
            ->willReturn(null);
        $repository->expects(self::never())->method('delete');

        $sut = new DeleteUserHandler($repository);
        $command = DeleteUser::with('nonexistent-user');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $sut->__invoke($command);
    }

    public function testDeleteCommandStoresUsername(): void
    {
        $command = DeleteUser::with('my-username');

        self::assertSame('my-username', $command->getUsername());
    }
}
