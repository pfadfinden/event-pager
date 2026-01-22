<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model\MessageAddressing;

use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;
use App\Core\TransportContract\Port\Transport;

/**
 * Represents a transport configuration that was selected during addressing.
 */
readonly class SelectedTransport
{
    public function __construct(
        public RecipientTransportConfiguration $configuration,
        public Transport $transport,
    ) {
    }
}
