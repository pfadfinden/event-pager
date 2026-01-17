<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\DataExchange\ReadModel\RecipientExportRow;

/**
 * @implements Query<iterable<RecipientExportRow>>
 */
final readonly class ExportRecipients implements Query
{
    public static function all(): self
    {
        return new self(null);
    }

    public static function ofType(string $type): self
    {
        return new self($type);
    }

    private function __construct(
        public ?string $filterType,
    ) {
    }
}
