<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Exception;

use Exception;

final class NtfySendFailed extends Exception
{
    public static function withReason(string $reason): self
    {
        return new self("Failed to send message to ntfy: {$reason}");
    }
}
