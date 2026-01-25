<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\ReadModel;

/**
 * Light DTO containing only the information needed to list message recipients (e.g. in a dropdown).
 */
final class RecipientListEntry
{
    /**
     * @param "GROUP"|"ROLE"|"PERSON" $type
     * @param list<string>            $enabledTransports Short class names of enabled transports for this recipient
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public array $enabledTransports = [],
    ) {
    }
}
