<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Command;

final readonly class SendMessage
{
    /**
     * @param string[] $to
     */
    public function __construct(
        public string $message,
        public string $sendBy,
        public int $priority,
        public array $to,
    ) {
    }
}
