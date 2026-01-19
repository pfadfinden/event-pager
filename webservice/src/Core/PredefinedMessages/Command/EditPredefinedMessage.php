<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Command;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

final readonly class EditPredefinedMessage
{
    /**
     * @param list<string> $recipientIds
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $messageContent,
        public int $priority,
        public array $recipientIds,
        public bool $isFavorite,
        public int $sortOrder,
        public bool $isEnabled,
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
