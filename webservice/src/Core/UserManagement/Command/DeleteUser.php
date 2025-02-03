<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Command;

readonly class DeleteUser
{
    /*
    * @param string $username
    * @return self
    */
    public static function with(
        string $username,
    ): self {
        return new self($username);
    }

    public function __construct(
        private string $username,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
