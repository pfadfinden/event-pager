<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Command;

/**
 * Links (or, when recipientId is null, unlinks) a user to a message recipient.
 */
readonly class LinkRecipientToUser
{
    public static function with(string $username, ?string $recipientId): self
    {
        return new self($username, $recipientId);
    }

    private function __construct(
        private string $username,
        private ?string $recipientId,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRecipientId(): ?string
    {
        return $this->recipientId;
    }
}
