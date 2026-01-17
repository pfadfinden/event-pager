<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

/**
 * Interface for export row DTOs that can be serialized to various formats.
 */
interface ExportRowInterface
{
    /**
     * @return string[]
     */
    public static function csvHeaders(): array;

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array;
}
