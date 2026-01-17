<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Model;

final readonly class ImportResult
{
    /**
     * @param string[] $errors
     * @param string[] $skipped
     */
    public function __construct(
        public int $imported,
        public int $updated,
        public int $skippedCount,
        public array $errors,
        public array $skipped,
    ) {
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }

    public function totalProcessed(): int
    {
        return $this->imported + $this->updated + $this->skippedCount;
    }
}
