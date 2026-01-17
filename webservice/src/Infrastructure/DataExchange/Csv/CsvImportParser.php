<?php

declare(strict_types=1);

namespace App\Infrastructure\DataExchange\Csv;

use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Port\ImportParser;
use League\Csv\Reader;
use function sprintf;

final class CsvImportParser implements ImportParser
{
    public function getFormat(): string
    {
        return 'csv';
    }

    /**
     * @return iterable<array<string, string>>
     */
    public function parseFile(string $filePath): iterable
    {
        $csv = Reader::from($filePath);
        $csv->setDelimiter(',');
        $csv->setHeaderOffset(0);

        return $csv->getRecords();
    }

    /**
     * @return iterable<array<string, string>>
     */
    public function parse(string $content): iterable
    {
        $csv = Reader::fromString($content);
        $csv->setDelimiter(',');
        $csv->setHeaderOffset(0);

        return $csv->getRecords();
    }

    /**
     * @param string[] $expectedHeaders
     * @param string[] $actualHeaders
     */
    public function validateHeaders(array $expectedHeaders, array $actualHeaders): void
    {
        $missing = array_diff($expectedHeaders, $actualHeaders);
        if ([] !== $missing) {
            throw new ImportValidationException(sprintf('Missing required columns: %s', implode(', ', $missing)));
        }
    }
}
