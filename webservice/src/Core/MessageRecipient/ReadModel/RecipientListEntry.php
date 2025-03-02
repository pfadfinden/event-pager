<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\ReadModel;

/**
 * Light DTO containing only the information needed to list message recipients (e.g. in a dropdown).
 */
final class RecipientListEntry
{
    /**
     * @param "Group"|"Role"|"Person" $type
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
    ) {
    }
}
