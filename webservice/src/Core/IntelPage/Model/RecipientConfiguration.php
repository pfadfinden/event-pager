<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use App\Core\TransportContract\Model\Priority;
use function array_key_exists;

/**
 * @todo TEST
 */
final readonly class RecipientConfiguration
{
    public const Priority DEFAULT_ALERT_FROM_PRIORITY = Priority::HIGH;
    public const string KEY_CHANNEL = 'channel';
    public const string KEY_ALERT_FROM_PRIORITY = 'alert_from_priority';

    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function __construct(public array $config)
    {
    }

    public function hasChannelConfiguration(): bool
    {
        return array_key_exists(self::KEY_CHANNEL, $this->config);
    }

    public function channelId(): string
    {
        return $this->config[self::KEY_CHANNEL];
    }

    public function alertFromPriority(): Priority
    {
        return Priority::tryFrom(
            $this->config[self::KEY_ALERT_FROM_PRIORITY]
                ?? self::DEFAULT_ALERT_FROM_PRIORITY->value
        ) ?? self::DEFAULT_ALERT_FROM_PRIORITY;
    }
}
