<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\DataExchange\ReadModel\MessageHistoryExportRow;
use DateTimeImmutable;

/**
 * @implements Query<iterable<MessageHistoryExportRow>>
 */
final readonly class ExportMessageHistory implements Query
{
    public static function all(): self
    {
        return new self(null, null);
    }

    public static function inDateRange(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self($from, $to);
    }

    private function __construct(
        public ?DateTimeImmutable $from,
        public ?DateTimeImmutable $to,
    ) {
    }
}
