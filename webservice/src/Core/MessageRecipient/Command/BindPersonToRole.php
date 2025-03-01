<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Command;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

/**
 * Invoke with value null for the person to remove a person from a role.
 */
final readonly class BindPersonToRole
{
    public function __construct(
        public string $roleID,
        public ?string $personID,
    ) {
    }

    public function getPersonID(): ?Ulid
    {
        if (null === $this->personID) {
            return null;
        }

        if (!Ulid::isValid($this->personID)) {
            throw new InvalidArgumentException('Malformed person ID');
        }

        return Ulid::fromString($this->personID);
    }

    public function getRoleID(): Ulid
    {
        return Ulid::fromString($this->roleID);
    }
}
