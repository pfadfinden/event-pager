<?php

declare(strict_types=1);

namespace App\Infrastructure\DataExchange\Csv;

use App\Core\DataExchange\Port\ExportFormatter;
use App\Core\DataExchange\ReadModel\ExportRowInterface;
use Generator;
use League\Csv\Writer;

final class CsvExportFormatter implements ExportFormatter
{
    public function getFormat(): string
    {
        return 'csv';
    }

    public function getContentType(): string
    {
        return 'text/csv; charset=UTF-8';
    }

    public function getFileExtension(): string
    {
        return '.csv';
    }

    /**
     * @param iterable<ExportRowInterface> $rows
     * @param string[]                     $headers
     *
     * @return Generator<string>
     */
    public function formatStreaming(iterable $rows, array $headers): Generator
    {
        $csv = Writer::fromString();
        $csv->setDelimiter(',');
        $csv->insertOne($headers);
        yield $csv->toString();

        foreach ($rows as $row) {
            $csv = Writer::fromString();
            $csv->setDelimiter(',');
            $csv->insertOne(array_values($row->toArray()));
            yield $csv->toString();
        }
    }
}
