<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Command;

/**
 * Only removes transport, configuration objects on recipients are not touched.
 */
readonly class RemoveTransportConfiguration
{
    public function __construct(
        public string $key,
    ) {
    }
}
