<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

/**
 * Flat DTO for pager export. Slot assignments encoded as JSON.
 */
final readonly class PagerExportRow implements ExportRowInterface
{
    public function __construct(
        public string $id,
        public string $label,
        public int $number,
        public ?string $comment,
        public bool $activated,
        public ?string $carriedById,
        public ?string $slotAssignments,
    ) {
    }

    /**
     * @return string[]
     */
    public static function csvHeaders(): array
    {
        return ['id', 'label', 'number', 'comment', 'activated', 'carried_by_id', 'slot_assignments'];
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'number' => $this->number,
            'comment' => $this->comment ?? '',
            'activated' => $this->activated ? '1' : '0',
            'carried_by_id' => $this->carriedById ?? '',
            'slot_assignments' => $this->slotAssignments ?? '',
        ];
    }
}
