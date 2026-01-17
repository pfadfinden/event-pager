<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Command;

use SensitiveParameter;

readonly class AddOrUpdateTransportConfiguration
{
    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public static function with(
        string $key,
        string $transport,
        string $title,
        ?bool $enabled = false,
        #[SensitiveParameter]
        ?array $vendorSpecificConfiguration = null,
    ): self {
        // @phpstan-ignore-next-line class-string
        return new self($key, $transport, $title, $enabled, $vendorSpecificConfiguration);
    }

    /**
     * @param class-string $transport
     */
    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function __construct(
        public string $key,
        public string $transport,
        public string $title,
        public ?bool $enabled = false,
        #[SensitiveParameter]
        public ?array $vendorSpecificConfiguration = null,
    ) {
    }
}
