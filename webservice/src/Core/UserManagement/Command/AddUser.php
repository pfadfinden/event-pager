<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Command;

readonly class AddUser
{
    /*
    * @param string $username
    * @param string $password
    * @param string $displayName
    * @return self
    */
    public static function with(
        string $username,
        string $password,
        string $displayName
    ) : self {
        return new self($username, $password, $displayName);
    }

    public function __construct(
        private string $username,
        private string $password,
        private string $displayName,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }
}
