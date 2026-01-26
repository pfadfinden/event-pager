<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Application;

use App\Core\UserManagement\Application\AddUserHandler;
use App\Core\UserManagement\Command\AddUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

#[CoversClass(AddUser::class)]
#[CoversClass(AddUserHandler::class)]
#[Group('unit')]
final class AddUserHandlerTest extends TestCase
{
    private UserPasswordHasher $passwordHasher;

    protected function setUp(): void
    {
        $this->passwordHasher = new UserPasswordHasher(
            new PasswordHasherFactory([User::class => ['algorithm' => 'auto']])
        );
    }

    public function testAddUserCommand(): void
    {
        $repository = self::createMock(UserRepository::class);

        $repository->expects(self::once())->method('save')
            ->with(self::callback(function ($value): bool {
                return $value instanceof User
                    && 'test-user' === $value->getUsername()
                    && 'Test User' === $value->getDisplayname()
                    && 'secure-password' !== $value->getPassword(); // Password should be hashed
            }));

        $sut = new AddUserHandler($repository, $this->passwordHasher);
        $command = AddUser::with(
            'test-user',
            'secure-password',
            'Test User'
        );
        $sut->__invoke($command);
    }

    public function testAddUserWithEmptyDisplayname(): void
    {
        $repository = self::createMock(UserRepository::class);

        $repository->expects(self::once())->method('save')
            ->with(self::callback(function ($value): bool {
                return $value instanceof User
                    && 'test-user' === $value->getUsername()
                    && '' === $value->getDisplayname();
            }));

        $sut = new AddUserHandler($repository, $this->passwordHasher);
        $command = AddUser::with(
            'test-user',
            'secure-password',
            ''
        );
        $sut->__invoke($command);
    }

    public function testAddUserCommandStoresValues(): void
    {
        $command = AddUser::with(
            'my-username',
            'my-password',
            'My Display Name'
        );

        self::assertSame('my-username', $command->getUsername());
        self::assertSame('my-password', $command->getPassword());
        self::assertSame('My Display Name', $command->getDisplayName());
    }

    public function testPasswordIsProperlyHashed(): void
    {
        $repository = self::createMock(UserRepository::class);
        $capturedUser = null;

        $repository->expects(self::once())->method('save')
            ->willReturnCallback(function (User $user) use (&$capturedUser): void {
                $capturedUser = $user;
            });

        $sut = new AddUserHandler($repository, $this->passwordHasher);
        $command = AddUser::with('test-user', 'test-password', 'Test User');
        $sut->__invoke($command);

        self::assertNotNull($capturedUser);
        self::assertNotSame('test-password', $capturedUser->getPassword());
        self::assertTrue(password_verify('test-password', $capturedUser->getPassword()));
    }
}
