<?php

namespace App\Core\SendMessage\ReadModel;

readonly class SendMessageStatus
{
    public function __construct(
        public string $messageId,
        public string $sendOn,
        public string $sendBy,
        public string $content,
        public string $priority,
        public string $status,
    ) {
    }
}
