<?php

declare(strict_types=1);

namespace App\Core\OutgoingMessage\Model;

/**
 * Configuration object associated with one MessageRecipient and one Transport.
 */
final readonly class RecipientTransportConfig
{
    // @phpstan-ignore-next-line missingType.iterableValue (JSON compatible array)
    public function __construct(
        /**
         * Identifier of the {@link SystemTransportConfig} this {@link RecipientTransportConfig} relates to.
         */
        public string $key,
        /**
         * Configuration options specific to the transport vendor, e.g. group names, phone numbers, id's.
         */
        public array $vendorSpecificConfig,
    ) {
    }
}
