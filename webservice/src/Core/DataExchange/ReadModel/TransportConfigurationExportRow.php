<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

final readonly class TransportConfigurationExportRow implements ExportRowInterface
{
    public function __construct(
        public string $key,
        public string $transport,
        public string $title,
        public bool $enabled,
        public ?string $vendorSpecificConfig,
    ) {
    }

    /**
     * @return string[]
     */
    public static function csvHeaders(): array
    {
        return ['key', 'transport', 'title', 'enabled', 'vendor_specific_config'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'transport' => $this->transport,
            'title' => $this->title,
            'enabled' => $this->enabled ? '1' : '0',
            'vendor_specific_config' => $this->vendorSpecificConfig ?? '',
        ];
    }
}
