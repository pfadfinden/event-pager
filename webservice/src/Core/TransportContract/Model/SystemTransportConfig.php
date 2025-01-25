<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

/**
 * This entity enables administrators to manage transport
 * through the application instead of through configuration
 * files to improve easy of administration.
 *
 * A SystemTransportConfig combines a technical transport
 * implementation with a user readable title and deployment
 * specific configuration (e.g. API Keys).
 * There can be multiple configurations for one transport.
 */
interface SystemTransportConfig
{
    /**
     * @return string identifier of this transport
     */
    public function getKey(): string;

    /**
     * @returns ?array vendor-specific json-compatible configuration data, e.g. API Keys
     */
    public function getVendorSpecificConfig(): ?array; // @phpstan-ignore missingType.iterableValue (JSON compatible array)
}
