<?php

declare(strict_types=1);

namespace App\Tests\Factory\Story\Default;

use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

/**
 * Creates default system users matching the CSV fixture data.
 *
 * Users created:
 * - admin (ROLE_ADMIN)
 * - manager (ROLE_MANAGER)
 * - support (ROLE_SUPPORT)
 * - user (no special roles)
 *
 * All users have their username as password for development/testing.
 *
 * Use: UserStory::load();
 */
#[AsFixture(name: 'default-users', groups: ['default', 'bdp-scout-event-sample-de'])]
final class UserStory extends Story
{
    public function build(): void
    {
        // Admin user
        UserFactory::createOne([
            'username' => 'admin',
            'displayname' => 'admin',
            'roles' => ['ROLE_ADMIN'],
            'password' => 'admin',
        ]);

        // Manager user
        UserFactory::createOne([
            'username' => 'manager',
            'displayname' => 'manager',
            'roles' => ['ROLE_MANAGER'],
            'password' => 'manager',
        ]);

        // Support user
        UserFactory::createOne([
            'username' => 'support',
            'displayname' => 'support',
            'roles' => ['ROLE_SUPPORT'],
            'password' => 'support',
        ]);

        // Regular user
        UserFactory::createOne([
            'username' => 'user',
            'displayname' => 'user',
            'roles' => [],
            'password' => 'user',
        ]);
    }
}
