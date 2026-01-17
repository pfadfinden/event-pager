<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Model;

enum ExportFormat: string
{
    case CSV = 'csv';
    // Future formats:
    // case JSON = 'json';
    // case XLSX = 'xlsx';
}
