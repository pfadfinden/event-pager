<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\ReadModel;

/**
 * Detailed DTO for viewing a single message recipient with type-specific data.
 */
final class RecipientDetail
{
    /**
     * @param "GROUP"|"ROLE"|"PERSON"                    $type
     * @param array<RecipientListEntry>                  $members                 For groups: list of direct members
     * @param array<RecipientListEntry>                  $groups                  Groups this recipient belongs to
     * @param RecipientListEntry|null                    $assignedPerson          For roles: the person assigned to this role
     * @param array<RecipientListEntry>                  $assignedRoles           For persons: roles assigned to this person
     * @param array<string, TransportConfigurationEntry> $transportConfigurations Transport configurations keyed by config ID, sorted by rank descending
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public array $members = [],
        public array $groups = [],
        public ?RecipientListEntry $assignedPerson = null,
        public array $assignedRoles = [],
        public array $transportConfigurations = [],
    ) {
    }

    public function isGroup(): bool
    {
        return 'GROUP' === $this->type;
    }

    public function isRole(): bool
    {
        return 'ROLE' === $this->type;
    }

    public function isPerson(): bool
    {
        return 'PERSON' === $this->type;
    }
}
