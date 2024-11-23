<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Event;

final readonly class NewMessageReceived
{
    public function __construct(public string $messageId)
    {
    }
}
