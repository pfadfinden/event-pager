<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

use App\Core\TransportContract\Port\Transport;
use Symfony\Component\Uid\Ulid;

/**
 * Parameter object for Transport::send() method.
 *
 * @see Transport::send()
 */
readonly class OutgoingMessage
{
    public static function failure(MessageRecipient $recipient, Message $incomingMessage): self
    {
        return new self(
            Ulid::fromString(Ulid::generate()),
            $recipient,
            $incomingMessage,
            '_FAILED_'
        );
    }

    public static function for(MessageRecipient $recipient, Message $incomingMessage, Transport $onTransport): self
    {
        return new self(
            Ulid::fromString(Ulid::generate()),
            $recipient,
            $incomingMessage,
            $onTransport->key()
        );
    }

    private function __construct(
        public Ulid $id,
        public MessageRecipient $recipient,
        public Message $incomingMessage,
        public string $transport,
    ) {
    }
}
