<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\ReadModel;

final readonly class PredefinedMessageListEntry
{
    public function __construct(
        public string $id,
        public string $title,
        public string $messageContent,
        public int $priority,
        public bool $isFavorite,
        public int $sortOrder,
        public bool $isEnabled,
    ) {
    }
}
