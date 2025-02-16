<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Command;

readonly class EditUser
{
    /**
     * @param ?string[] $add_roles
     * @param ?string[] $remove_roles
     */
    public static function with(
        string $username,
        ?string $password,
        ?string $displayname,
        ?array $add_roles,
        ?array $remove_roles,
    ): self {
        return new self($username, $password, $displayname, $add_roles, $remove_roles);
    }

    /**
     * @param ?string[] $add_roles
     * @param ?string[] $revoke_roles
     */
    public function __construct(
        private string $username,
        private ?string $password,
        private ?string $displayname,
        private ?array $add_roles,
        private ?array $revoke_roles,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    /**
     * @return string[]|null
     */
    public function getAddRoles(): ?array
    {
        return $this->add_roles;
    }

    /**
     * @return string[]|null
     */
    public function getRevokeRoles(): ?array
    {
        return $this->revoke_roles;
    }
}
