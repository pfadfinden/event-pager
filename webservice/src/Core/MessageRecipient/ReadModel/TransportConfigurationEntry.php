<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\ReadModel;

/**
 * DTO for transport configuration in recipient detail views.
 */
final class TransportConfigurationEntry
{
    /**
     * @param array<mixed>|null $vendorSpecificConfig
     */
    public function __construct(
        public string $id,
        public string $key,
        public bool $isEnabled,
        public ?array $vendorSpecificConfig = null,
    ) {
    }
}
