<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\DataExchange\ReadModel\PagerExportRow;

/**
 * @implements Query<iterable<PagerExportRow>>
 */
final readonly class ExportPagers implements Query
{
    public static function all(): self
    {
        return new self(false);
    }

    public static function activeOnly(): self
    {
        return new self(true);
    }

    private function __construct(
        public bool $activeOnly,
    ) {
    }
}
