<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\Model;

use App\Core\UserManagement\Model\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[Group('unit')]
final class UserTest extends TestCase
{
    public function testCanCreateUserWithUsername(): void
    {
        $user = new User('johndoe');

        self::assertSame('johndoe', $user->getUsername());
        self::assertSame('johndoe', $user->getUserIdentifier());
        self::assertNull($user->getId());
    }

    public function testCanSetAndGetExternalId(): void
    {
        $user = new User('johndoe');
        self::assertNull($user->getExternalId());

        $result = $user->setExternalId('kc-uuid-12345');

        self::assertSame($user, $result); // Fluent interface
        self::assertSame('kc-uuid-12345', $user->getExternalId());
    }

    public function testCanSetExternalIdToNull(): void
    {
        $user = new User('johndoe');
        $user->setExternalId('kc-uuid-12345');

        $user->setExternalId(null);

        self::assertNull($user->getExternalId());
    }

    public function testCanSetAndGetDisplayname(): void
    {
        $user = new User('johndoe');
        self::assertNull($user->getDisplayname());

        $user->setDisplayname('John Doe');

        self::assertSame('John Doe', $user->getDisplayname());
    }

    public function testCanSetAndGetUsername(): void
    {
        $user = new User('johndoe');

        $user->setUsername('janedoe');

        self::assertSame('janedoe', $user->getUsername());
    }

    public function testNewUserHasEmptyRoles(): void
    {
        $user = new User('johndoe');

        self::assertSame([], $user->getRoles());
    }

    public function testCanSetRoles(): void
    {
        $user = new User('johndoe');

        $user->setRoles(['ROLE_ADMIN', 'ROLE_MANAGER']);

        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
    }

    public function testCanAddRoles(): void
    {
        $user = new User('johndoe');
        $user->setRoles(['ROLE_ADMIN']);

        $user->addRoles(['ROLE_MANAGER', 'ROLE_SUPPORT']);

        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
        self::assertContains('ROLE_SUPPORT', $user->getRoles());
    }

    public function testCanRemoveRole(): void
    {
        $user = new User('johndoe');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_MANAGER']);

        $user->removeRole('ROLE_ADMIN');

        self::assertNotContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_MANAGER', $user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $user = new User('johndoe');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);

        $roles = $user->getRoles();

        self::assertSame($roles, array_unique($roles));
    }

    public function testCanSetAndGetPassword(): void
    {
        $user = new User('johndoe');
        self::assertSame('', $user->getPassword());

        $user->setPassword('hashedpassword123');

        self::assertSame('hashedpassword123', $user->getPassword());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $user = new User('johndoe');
        $user->eraseCredentials();
    }
}
