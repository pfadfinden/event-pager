<?php

declare(strict_types=1);

namespace App\Core\TelegramTransport\Exception;

use Exception;

final class TelegramSendFailed extends Exception
{
    public static function withReason(string $reason): self
    {
        return new self("Failed to send message to Telegram: {$reason}");
    }
}
