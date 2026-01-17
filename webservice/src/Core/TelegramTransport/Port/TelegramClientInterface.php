<?php

declare(strict_types=1);

namespace App\Core\TelegramTransport\Port;

use App\Core\TelegramTransport\Exception\TelegramSendFailed;

/**
 * Interface for sending messages to Telegram.
 */
interface TelegramClientInterface
{
    /**
     * Send a message to a Telegram chat.
     *
     * @param string $botToken The Telegram bot token
     * @param string $chatId   The target chat ID (user or group)
     * @param string $message  The message body
     *
     * @throws TelegramSendFailed
     */
    public function send(
        string $botToken,
        string $chatId,
        string $message,
    ): void;
}
