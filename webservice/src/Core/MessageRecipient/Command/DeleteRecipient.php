<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Command;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

final readonly class DeleteRecipient
{
    public function __construct(
        public string $recipientID,
    ) {
    }

    public function getRecipientID(): Ulid
    {
        if (!Ulid::isValid($this->recipientID)) {
            throw new InvalidArgumentException('Malformed recipient ID');
        }

        return Ulid::fromString($this->recipientID);
    }
}
