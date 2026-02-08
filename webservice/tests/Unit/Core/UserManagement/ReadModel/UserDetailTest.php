<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\ReadModel;

use App\Core\UserManagement\ReadModel\UserDetail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserDetail::class)]
#[Group('unit')]
final class UserDetailTest extends TestCase
{
    public function testCanBeConstructedWithRequiredParameters(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: ['ROLE_USER'],
        );

        self::assertSame(1, $userDetail->id);
        self::assertSame('johndoe', $userDetail->username);
        self::assertSame('John Doe', $userDetail->displayname);
        self::assertSame(['ROLE_USER'], $userDetail->roles);
        self::assertNull($userDetail->externalId);
        self::assertFalse($userDetail->hasPassword);
    }

    public function testCanBeConstructedWithAllParameters(): void
    {
        $userDetail = new UserDetail(
            id: 42,
            username: 'janedoe',
            displayname: 'Jane Doe',
            roles: ['ROLE_ADMIN', 'ROLE_MANAGER'],
            externalId: 'keycloak-uuid-12345',
            hasPassword: true,
        );

        self::assertSame(42, $userDetail->id);
        self::assertSame('janedoe', $userDetail->username);
        self::assertSame('Jane Doe', $userDetail->displayname);
        self::assertSame(['ROLE_ADMIN', 'ROLE_MANAGER'], $userDetail->roles);
        self::assertSame('keycloak-uuid-12345', $userDetail->externalId);
        self::assertTrue($userDetail->hasPassword);
    }

    public function testCanBeConstructedWithNullDisplayname(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: null,
            roles: [],
        );

        self::assertNull($userDetail->displayname);
    }

    public function testCanBeConstructedWithEmptyRoles(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: [],
        );

        self::assertSame([], $userDetail->roles);
    }

    public function testGetDisplayNameReturnsDisplaynameWhenSet(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: [],
        );

        self::assertSame('John Doe', $userDetail->getDisplayName());
    }

    public function testGetDisplayNameReturnsUsernameWhenDisplaynameIsNull(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: null,
            roles: [],
        );

        self::assertSame('johndoe', $userDetail->getDisplayName());
    }

    public function testHasPasswordDefaultsToFalse(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: [],
        );

        self::assertFalse($userDetail->hasPassword);
    }

    public function testExternalIdDefaultsToNull(): void
    {
        $userDetail = new UserDetail(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: [],
        );

        self::assertNull($userDetail->externalId);
    }

    public function testPropertiesArePubliclyReadable(): void
    {
        $userDetail = new UserDetail(
            id: 123,
            username: 'testuser',
            displayname: 'Test User',
            roles: ['ROLE_SUPPORT'],
            externalId: 'ext-id',
            hasPassword: true,
        );

        // Verify all public properties are accessible
        self::assertIsInt($userDetail->id);
        self::assertIsString($userDetail->username);
        self::assertIsString($userDetail->displayname);
        self::assertIsArray($userDetail->roles);
        self::assertIsString($userDetail->externalId);
        self::assertIsBool($userDetail->hasPassword);
    }
}
