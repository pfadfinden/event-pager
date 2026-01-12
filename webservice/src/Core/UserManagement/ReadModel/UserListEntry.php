<?php

declare(strict_types=1);

namespace App\Core\UserManagement\ReadModel;

/**
 * Light DTO containing only the information needed to list users.
 */
final class UserListEntry
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public int $id,
        public string $username,
        public ?string $displayname,
        public array $roles,
    ) {
    }
}
