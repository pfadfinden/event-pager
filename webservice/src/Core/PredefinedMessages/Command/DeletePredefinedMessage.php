<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Command;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

final readonly class DeletePredefinedMessage
{
    public function __construct(
        public string $id,
    ) {
    }

    public function getId(): Ulid
    {
        if (!Ulid::isValid($this->id)) {
            throw new InvalidArgumentException('Malformed predefined message ID');
        }

        return Ulid::fromString($this->id);
    }
}
