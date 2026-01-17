<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Command;

use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;

final readonly class ImportPagers
{
    public static function fromFile(
        string $filePath,
        ExportFormat $format = ExportFormat::CSV,
        ImportConflictStrategy $conflictStrategy = ImportConflictStrategy::SKIP,
        bool $importSlotAssignments = true,
    ): self {
        return new self($filePath, null, $format, $conflictStrategy, $importSlotAssignments);
    }

    public static function fromContent(
        string $content,
        ExportFormat $format = ExportFormat::CSV,
        ImportConflictStrategy $conflictStrategy = ImportConflictStrategy::SKIP,
        bool $importSlotAssignments = true,
    ): self {
        return new self(null, $content, $format, $conflictStrategy, $importSlotAssignments);
    }

    private function __construct(
        public ?string $filePath,
        public ?string $content,
        public ExportFormat $format,
        public ImportConflictStrategy $conflictStrategy,
        public bool $importSlotAssignments,
    ) {
    }
}
