<?php

declare(strict_types=1);

namespace App\Core\UserManagement\ReadModel;

/**
 * Detailed DTO for viewing a single user.
 */
final class UserDetail
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public int $id,
        public string $username,
        public ?string $displayname,
        public array $roles,
        public ?string $externalId = null,
        public bool $hasPassword = false,
    ) {
    }

    public function getDisplayName(): string
    {
        return $this->displayname ?? $this->username;
    }
}
