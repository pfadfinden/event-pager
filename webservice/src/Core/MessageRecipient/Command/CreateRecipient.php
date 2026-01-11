<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Command;

final readonly class CreateRecipient
{
    public function __construct(
        public string $id,
        public string $recipientType,
        public string $name,
    ) {
    }
}
