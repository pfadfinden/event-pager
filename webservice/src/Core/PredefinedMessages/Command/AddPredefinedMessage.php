<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Command;

final readonly class AddPredefinedMessage
{
    /**
     * @param list<string> $recipientIds
     */
    public function __construct(
        public string $title,
        public string $messageContent,
        public int $priority,
        public array $recipientIds,
        public bool $isFavorite = false,
        public int $sortOrder = 0,
        public bool $isEnabled = true,
    ) {
    }
}
