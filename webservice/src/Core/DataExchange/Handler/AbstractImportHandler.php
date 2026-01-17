<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Handler;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\DataExchange\Exception\ImportValidationException;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use Throwable;
use function array_diff;
use function array_keys;
use function count;
use function implode;
use function sprintf;

/**
 * Base class for import handlers providing common import loop and validation logic.
 */
abstract class AbstractImportHandler
{
    public function __construct(
        protected readonly FormatAdapterFactory $formatFactory,
        protected readonly UnitOfWork $uow,
    ) {
    }

    /**
     * @return string[]
     */
    abstract protected function getRequiredHeaders(): array;

    /**
     * Returns the identifier field name for the entity being imported.
     */
    abstract protected function getIdentifierField(): string;

    /**
     * Execute the import process.
     *
     * @param callable(array<string, string>, int): string $processRow
     */
    protected function executeImport(
        ?string $filePath,
        ?string $content,
        ExportFormat $format,
        ImportConflictStrategy $conflictStrategy,
        callable $processRow,
    ): ImportResult {
        $parser = $this->formatFactory->createParser($format);

        $rows = null !== $filePath
            ? $parser->parseFile($filePath)
            : $parser->parse($content ?? '');

        $imported = 0;
        $updated = 0;
        $skipped = [];
        $errors = [];
        $index = 0;
        $idField = $this->getIdentifierField();

        foreach ($rows as $row) {
            try {
                $this->validateRow($row, $index);
                $result = $processRow($row, $index);
                match ($result) {
                    'imported' => $imported++,
                    'updated' => $updated++,
                    'skipped' => $skipped[] = $row[$idField] ?? "row {$index}",
                };
            } catch (Throwable $e) {
                $errors[] = sprintf('Row %d: %s', $index + 1, $e->getMessage());
            }
            ++$index;
        }

        $this->uow->commit();

        return new ImportResult($imported, $updated, count($skipped), $errors, $skipped);
    }

    /**
     * Validate that a row contains all required headers.
     *
     * @param array<string, string> $row
     *
     * @throws ImportValidationException
     */
    protected function validateRow(array $row, int $index): void
    {
        $required = $this->getRequiredHeaders();
        $missing = array_diff($required, array_keys($row));

        if ([] !== $missing) {
            throw new ImportValidationException(sprintf('Row %d: Missing required columns: %s', $index + 1, implode(', ', $missing)));
        }
    }

    /**
     * Parse boolean value from CSV string.
     */
    protected function parseBool(string $value): bool
    {
        return '1' === $value || 'true' === strtolower($value);
    }
}
