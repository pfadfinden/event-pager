<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Port;

use App\Core\DataExchange\ReadModel\ExportRowInterface;
use Generator;

/**
 * Port interface for formatting export data into specific formats.
 * Infrastructure adapters implement this for CSV, JSON, etc.
 */
interface ExportFormatter
{
    /**
     * Returns the format identifier (e.g., 'csv', 'json').
     */
    public function getFormat(): string;

    /**
     * Returns appropriate content type for HTTP response.
     */
    public function getContentType(): string;

    /**
     * Returns file extension including dot (e.g., '.csv').
     */
    public function getFileExtension(): string;

    /**
     * Stream format for large datasets - returns generator.
     *
     * @param iterable<ExportRowInterface> $rows    Data transfer objects to export
     * @param string[]                     $headers Column headers
     *
     * @return Generator<string>
     */
    public function formatStreaming(iterable $rows, array $headers): Generator;
}
