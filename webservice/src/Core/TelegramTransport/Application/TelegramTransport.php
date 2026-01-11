<?php

declare(strict_types=1);

namespace App\Core\TelegramTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\TelegramTransport\Exception\TelegramSendFailed;
use App\Core\TelegramTransport\Model\RecipientConfiguration;
use App\Core\TelegramTransport\Port\TelegramClientInterface;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\MessageRecipient;
use App\Core\TransportContract\Model\OutgoingMessage;
use App\Core\TransportContract\Model\OutgoingMessageEvent;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;

/**
 * Transport implementation for Telegram messages.
 *
 * Sends messages synchronously via the Telegram Bot API to configured chats.
 *
 * @see https://core.telegram.org/bots/api#sendmessage
 */
final readonly class TelegramTransport implements Transport
{
    public const string CONFIG_BOT_TOKEN = 'botToken';

    public function __construct(
        private SystemTransportConfig $config,
        private TelegramClientInterface $telegramClient,
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
            && isset($vendorConfig[self::CONFIG_BOT_TOKEN])
            && '' !== $vendorConfig[self::CONFIG_BOT_TOKEN];
    }

    public function canSendTo(MessageRecipient $recipient, Message $incomingMessage): bool
    {
        $config = $recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            return false;
        }

        $recipientConfiguration = new RecipientConfiguration($config);

        return $recipientConfiguration->hasChatId();
    }

    public function send(OutgoingMessage $message): void
    {
        $config = $message->recipient->getTransportConfigurationFor($this);
        if (null === $config) {
            $this->publishFailedEvent($message);

            return;
        }

        $recipientConfiguration = new RecipientConfiguration($config);
        if (!$recipientConfiguration->hasChatId()) {
            $this->publishFailedEvent($message);

            return;
        }

        $vendorConfig = $this->config->getVendorSpecificConfig();
        if (null === $vendorConfig) {
            $this->publishFailedEvent($message);

            return;
        }

        $botToken = $vendorConfig[self::CONFIG_BOT_TOKEN] ?? null;
        if (null === $botToken || '' === $botToken) {
            $this->publishFailedEvent($message);

            return;
        }

        try {
            $this->telegramClient->send(
                $botToken,
                $recipientConfiguration->chatId(),
                $message->incomingMessage->body,
            );

            $this->eventBus->publish(OutgoingMessageEvent::transmitted(
                $message->id,
            ));
        } catch (TelegramSendFailed) {
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
