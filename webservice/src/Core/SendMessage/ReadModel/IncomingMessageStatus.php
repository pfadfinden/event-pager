<?php

declare(strict_types=1);

namespace App\Core\SendMessage\ReadModel;

readonly class IncomingMessageStatus
{
    public function __construct(
        public string $messageId,
        public string $sentOn,
        public string $sentBy,
        public string $content,
        public string $priority,
        public string $status,
    ) {
    }
}
