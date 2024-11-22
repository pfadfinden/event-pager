<?php

declare(strict_types=1);

namespace App\Core\MessageAddressing;

use App\Core\MessageRecipient\Model\MessageRecipient;

interface Transport
{
    public function canSendTo(MessageRecipient $recipient, Priority $priority): bool;

    public function send(MessageRecipient $recipient, Priority $priority, string $message): void;
}
