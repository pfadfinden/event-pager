<?php

declare(strict_types=1);

namespace App\Core\TelegramTransport\Model;

use function array_key_exists;

/**
 * Value object for parsing recipient-specific Telegram configuration.
 */
final readonly class RecipientConfiguration
{
    public const string KEY_CHAT_ID = 'chatId';

    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function __construct(public array $config)
    {
    }

    public function hasChatId(): bool
    {
        return array_key_exists(self::KEY_CHAT_ID, $this->config)
            && '' !== $this->config[self::KEY_CHAT_ID];
    }

    public function chatId(): string
    {
        return $this->config[self::KEY_CHAT_ID];
    }
}
