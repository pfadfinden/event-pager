<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\DataExchange\ReadModel\TransportConfigurationExportRow;

/**
 * @implements Query<iterable<TransportConfigurationExportRow>>
 */
final readonly class ExportTransportConfigurations implements Query
{
    public static function all(): self
    {
        return new self(null);
    }

    public static function enabledOnly(): self
    {
        return new self(true);
    }

    private function __construct(
        public ?bool $enabledOnly,
    ) {
    }
}
