<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Command;

final readonly class ProcessIncomingMessage
{
    public function __construct(
        public string $id,
    ) {
    }
}
