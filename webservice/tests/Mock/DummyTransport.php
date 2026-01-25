<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Port\Transport;

final class DummyTransport implements Transport
{
    public function key(): string
    {
        return 'dummy-key';
    }

    public function acceptsNewMessages(): bool
    {
        return false;
    }

    public function canSendTo(MessageRecipient $recipient, Message $incomingMessage, ?array $recipientConfiguration): bool
    {
        return false;
    }

    public function send(OutgoingMessage $message): void
    {
        // TODO: Implement send() method.
    }
}
