<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Application;

use App\Core\UserManagement\Application\EditUserHandler;
use App\Core\UserManagement\Command\EditUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use function in_array;

#[CoversClass(EditUser::class)]
#[CoversClass(EditUserHandler::class)]
#[Group('unit')]
final class EditUserHandlerTest extends TestCase
{
    private UserPasswordHasher $passwordHasher;

    protected function setUp(): void
    {
        $this->passwordHasher = new UserPasswordHasher(
            new PasswordHasherFactory([User::class => ['algorithm' => 'auto']])
        );
    }

    public function testCanUpdatePassword(): void
    {
        $user = new User('test-user');
        $user->setPassword('old-hashed-password');

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                // Password should be hashed (different from plain text)
                return 'new-password' !== $savedUser->getPassword()
                    && '' !== $savedUser->getPassword();
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', 'new-password', null, null, null);

        $handler->__invoke($command);
    }

    public function testCanUpdateDisplayname(): void
    {
        $user = new User('test-user');
        $user->setDisplayname('Old Name');

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (User $savedUser): bool => 'New Display Name' === $savedUser->getDisplayname()));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', null, 'New Display Name', null, null);

        $handler->__invoke($command);
    }

    public function testCanAddRoles(): void
    {
        $user = new User('test-user');
        $user->setRoles(['ROLE_USER']);

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                $roles = $savedUser->getRoles();

                return in_array('ROLE_USER', $roles, true)
                    && in_array('ROLE_ADMIN', $roles, true)
                    && in_array('ROLE_MANAGER', $roles, true);
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', null, null, ['ROLE_ADMIN', 'ROLE_MANAGER'], null);

        $handler->__invoke($command);
    }

    public function testCanRevokeRoles(): void
    {
        $user = new User('test-user');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER']);

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                $roles = $savedUser->getRoles();

                return !in_array('ROLE_ADMIN', $roles, true)
                    && in_array('ROLE_MANAGER', $roles, true)
                    && in_array('ROLE_USER', $roles, true);
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', null, null, null, ['ROLE_ADMIN']);

        $handler->__invoke($command);
    }

    public function testCanAddAndRevokeRolesSimultaneously(): void
    {
        $user = new User('test-user');
        $user->setRoles(['ROLE_USER', 'ROLE_SUPPORT']);

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                $roles = $savedUser->getRoles();

                return in_array('ROLE_USER', $roles, true)
                    && in_array('ROLE_MANAGER', $roles, true)
                    && !in_array('ROLE_SUPPORT', $roles, true);
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', null, null, ['ROLE_MANAGER'], ['ROLE_SUPPORT']);

        $handler->__invoke($command);
    }

    public function testCanUpdateMultipleFieldsAtOnce(): void
    {
        $user = new User('test-user');
        $user->setDisplayname('Old Name');
        $user->setRoles(['ROLE_USER']);

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                return 'New Name' === $savedUser->getDisplayname()
                    && in_array('ROLE_ADMIN', $savedUser->getRoles(), true)
                    && 'new-password' !== $savedUser->getPassword()
                    && '' !== $savedUser->getPassword();
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', 'new-password', 'New Name', ['ROLE_ADMIN'], null);

        $handler->__invoke($command);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('nonexistent-user')
            ->willReturn(null);
        $repository->expects(self::never())
            ->method('save');

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('nonexistent-user', null, 'New Name', null, null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $handler->__invoke($command);
    }

    public function testNoChangesWhenAllParametersAreNull(): void
    {
        $user = new User('test-user');
        $user->setDisplayname('Original Name');
        $user->setPassword('original-password');
        $user->setRoles(['ROLE_USER']);

        $repository = self::createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneByUsername')
            ->with('test-user')
            ->willReturn($user);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $savedUser): bool {
                return 'Original Name' === $savedUser->getDisplayname()
                    && 'original-password' === $savedUser->getPassword()
                    && ['ROLE_USER'] === $savedUser->getRoles();
            }));

        $handler = new EditUserHandler($repository, $this->passwordHasher);
        $command = EditUser::with('test-user', null, null, null, null);

        $handler->__invoke($command);
    }
}
