<?php

declare(strict_types=1);

namespace App\Infrastructure\DataExchange;

use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Port\ExportFormatter;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\Port\ImportParser;
use App\Infrastructure\DataExchange\Csv\CsvExportFormatter;
use App\Infrastructure\DataExchange\Csv\CsvImportParser;

final class DefaultFormatAdapterFactory implements FormatAdapterFactory
{
    public function createExporter(ExportFormat $format): ExportFormatter
    {
        return match ($format) {
            ExportFormat::CSV => new CsvExportFormatter(),
        };
    }

    public function createParser(ExportFormat $format): ImportParser
    {
        return match ($format) {
            ExportFormat::CSV => new CsvImportParser(),
        };
    }

    /**
     * @return ExportFormat[]
     */
    public function supportedFormats(): array
    {
        return [ExportFormat::CSV];
    }
}
