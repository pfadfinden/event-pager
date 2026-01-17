<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

/**
 * Flat DTO for CSV export - one row per recipient.
 * Transport configurations are JSON-encoded in a single column.
 */
final readonly class RecipientExportRow implements ExportRowInterface
{
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public ?string $assignedPersonId,
        public ?string $groupMemberIds,
        public ?string $transportConfigs,
    ) {
    }

    /**
     * @return string[]
     */
    public static function csvHeaders(): array
    {
        return ['id', 'type', 'name', 'assigned_person_id', 'group_member_ids', 'transport_configs'];
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'assigned_person_id' => $this->assignedPersonId ?? '',
            'group_member_ids' => $this->groupMemberIds ?? '',
            'transport_configs' => $this->transportConfigs ?? '',
        ];
    }
}
