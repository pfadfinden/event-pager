<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\UserManagement\ReadModel;

use App\Core\UserManagement\ReadModel\UserListEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserListEntry::class)]
#[Group('unit')]
final class UserListEntryTest extends TestCase
{
    public function testCanBeConstructedWithAllParameters(): void
    {
        $entry = new UserListEntry(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: ['ROLE_USER'],
        );

        self::assertSame(1, $entry->id);
        self::assertSame('johndoe', $entry->username);
        self::assertSame('John Doe', $entry->displayname);
        self::assertSame(['ROLE_USER'], $entry->roles);
    }

    public function testCanBeConstructedWithNullDisplayname(): void
    {
        $entry = new UserListEntry(
            id: 1,
            username: 'johndoe',
            displayname: null,
            roles: [],
        );

        self::assertNull($entry->displayname);
    }

    public function testCanBeConstructedWithEmptyRoles(): void
    {
        $entry = new UserListEntry(
            id: 1,
            username: 'johndoe',
            displayname: 'John Doe',
            roles: [],
        );

        self::assertSame([], $entry->roles);
    }

    public function testCanBeConstructedWithMultipleRoles(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER'];

        $entry = new UserListEntry(
            id: 42,
            username: 'admin',
            displayname: 'Administrator',
            roles: $roles,
        );

        self::assertSame($roles, $entry->roles);
        self::assertCount(3, $entry->roles);
    }

    public function testPropertiesArePubliclyReadable(): void
    {
        $entry = new UserListEntry(
            id: 123,
            username: 'testuser',
            displayname: 'Test User',
            roles: ['ROLE_SUPPORT'],
        );

        // Verify all public properties are accessible
        self::assertIsInt($entry->id);
        self::assertIsString($entry->username);
        self::assertIsString($entry->displayname);
        self::assertIsArray($entry->roles);
    }

    public function testDifferentEntriesHaveDifferentValues(): void
    {
        $entry1 = new UserListEntry(
            id: 1,
            username: 'user1',
            displayname: 'User One',
            roles: ['ROLE_USER'],
        );

        $entry2 = new UserListEntry(
            id: 2,
            username: 'user2',
            displayname: 'User Two',
            roles: ['ROLE_ADMIN'],
        );

        self::assertNotSame($entry1->id, $entry2->id);
        self::assertNotSame($entry1->username, $entry2->username);
        self::assertNotSame($entry1->displayname, $entry2->displayname);
        self::assertNotSame($entry1->roles, $entry2->roles);
    }
}
