<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Port;

use App\Core\DataExchange\Model\ExportFormat;

interface FormatAdapterFactory
{
    public function createExporter(ExportFormat $format): ExportFormatter;

    public function createParser(ExportFormat $format): ImportParser;

    /**
     * @return ExportFormat[]
     */
    public function supportedFormats(): array;
}
