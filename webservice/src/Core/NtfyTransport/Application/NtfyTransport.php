<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\NtfyTransport\Exception\NtfySendFailed;
use App\Core\NtfyTransport\Model\NtfyPriority;
use App\Core\NtfyTransport\Model\RecipientConfiguration;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;

/**
 * Transport implementation for ntfy push notifications.
 *
 * Sends messages synchronously via HTTP POST to a configured ntfy server.
 *
 * @see https://docs.ntfy.sh/publish/
 */
final readonly class NtfyTransport implements Transport
{
    public const string CONFIG_SERVER_URL = 'serverUrl';
    public const string CONFIG_ACCESS_TOKEN = 'accessToken';

    public function __construct(
        private SystemTransportConfig $config,
        private NtfyClientInterface $ntfyClient,
        private EventBus $eventBus,
    ) {
    }

    public function key(): string
    {
        return $this->config->getKey();
    }

    public function acceptsNewMessages(): bool
    {
        $vendorConfig = $this->config->getVendorSpecificConfig();

        return null !== $vendorConfig
            && isset($vendorConfig[self::CONFIG_SERVER_URL])
            && '' !== $vendorConfig[self::CONFIG_SERVER_URL];
    }

    public function canSendTo(MessageRecipient $recipient, Message $incomingMessage): bool
    {
        $config = $recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            return false;
        }

        $recipientConfiguration = new RecipientConfiguration($config);

        return $recipientConfiguration->hasTopic();
    }

    public function send(OutgoingMessage $message): void
    {
        $config = $message->recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            $this->publishFailedEvent($message);

            return;
        }

        $recipientConfiguration = new RecipientConfiguration($config);
        if (!$recipientConfiguration->hasTopic()) {
            $this->publishFailedEvent($message);

            return;
        }

        $vendorConfig = $this->config->getVendorSpecificConfig();
        if (null === $vendorConfig) {
            $this->publishFailedEvent($message);

            return;
        }

        $serverUrl = $vendorConfig[self::CONFIG_SERVER_URL] ?? null;
        if (null === $serverUrl || '' === $serverUrl) {
            $this->publishFailedEvent($message);

            return;
        }

        $accessToken = $vendorConfig[self::CONFIG_ACCESS_TOKEN] ?? null;

        try {
            $this->ntfyClient->send(
                $serverUrl,
                $recipientConfiguration->topic(),
                $message->incomingMessage->body,
                NtfyPriority::fromPriority($message->incomingMessage->priority),
                $accessToken,
            );

            $this->eventBus->publish(OutgoingMessageEvent::transmitted(
                $message->id,
            ));
        } catch (NtfySendFailed) {
            $this->publishFailedEvent($message);
        }
    }

    private function publishFailedEvent(OutgoingMessage $message): void
    {
        $this->eventBus->publish(OutgoingMessageEvent::failedToQueue(
            $message->id
        ));
    }
}
