<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Model;

use function array_key_exists;

/**
 * Value object for parsing recipient-specific ntfy configuration.
 */
final readonly class RecipientConfiguration
{
    public const string KEY_TOPIC = 'topic';

    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function __construct(public array $config)
    {
    }

    public function hasTopic(): bool
    {
        return array_key_exists(self::KEY_TOPIC, $this->config)
            && '' !== $this->config[self::KEY_TOPIC];
    }

    public function topic(): string
    {
        return $this->config[self::KEY_TOPIC];
    }
}
