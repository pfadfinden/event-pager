<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

final readonly class ChannelExportRow implements ExportRowInterface
{
    public function __construct(
        public string $id,
        public string $name,
        public int $capCode,
        public bool $audible,
        public bool $vibration,
    ) {
    }

    /**
     * @return string[]
     */
    public static function csvHeaders(): array
    {
        return ['id', 'name', 'cap_code', 'audible', 'vibration'];
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cap_code' => $this->capCode,
            'audible' => $this->audible ? '1' : '0',
            'vibration' => $this->vibration ? '1' : '0',
        ];
    }
}
