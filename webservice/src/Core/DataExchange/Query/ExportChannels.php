<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\DataExchange\ReadModel\ChannelExportRow;

/**
 * @implements Query<iterable<ChannelExportRow>>
 */
final readonly class ExportChannels implements Query
{
    public static function all(): self
    {
        return new self();
    }

    private function __construct()
    {
    }
}
