<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Command;

use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;

final readonly class ImportRecipients
{
    public static function fromFile(
        string $filePath,
        ExportFormat $format = ExportFormat::CSV,
        ImportConflictStrategy $conflictStrategy = ImportConflictStrategy::SKIP,
    ): self {
        return new self($filePath, null, $format, $conflictStrategy);
    }

    public static function fromContent(
        string $content,
        ExportFormat $format = ExportFormat::CSV,
        ImportConflictStrategy $conflictStrategy = ImportConflictStrategy::SKIP,
    ): self {
        return new self(null, $content, $format, $conflictStrategy);
    }

    private function __construct(
        public ?string $filePath,
        public ?string $content,
        public ExportFormat $format,
        public ImportConflictStrategy $conflictStrategy,
    ) {
    }
}
