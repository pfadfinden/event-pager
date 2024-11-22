<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Port;

use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\SystemTransportConfig;

/**
 * A Transport instance implements the logic to push (or queue) one
 * message to one delivery mechanism (e.g. Telegram, SMS, Email).
 *
 * A Transport instance is instantiated through the ::withSystemConfiguration factory method,
 * receiving configuration options such as API keys set by an administrator.
 * When creating a new transport, start by creating a Transport class.
 *
 * Transports are responsible to publish all applicable OutgoingMessageEvents.
 *
 * @see OutgoingMessageEvent
 */
interface Transport
{
    /**
     * static constructor method.
     */
    public static function withSystemConfiguration(SystemTransportConfig $config): static;

    /**
     * returns identifier as Slug.
     */
    public function key(): string;

    /**
     * Allows to check if the transport is configured correctly
     * to be able to send messages.
     */
    public function acceptsNewMessages(): bool;

    /**
     * Check if recipient is configured to use this transport and that its
     * configuration and the message itself is valid to be sent through
     * this transport.
     */
    public function canSendTo(MessageRecipient $recipient, Message $incomingMessage): bool;

    /**
     * Once a message was determined to be sent through this transport,
     * it should be passed to this method.
     *
     * The implementation of this method should queue the message for asynchronous processing
     */
    public function send(OutgoingMessage $message): void;
}
