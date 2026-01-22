<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

interface MessageRecipient extends \App\Core\TransportContract\Model\MessageRecipient
{
    public function getName(): string;

    /**
     * Returns all transport configurations sorted by rank descending (highest rank first).
     *
     * @return list<RecipientTransportConfiguration>
     */
    public function getTransportConfiguration(): array;
}
