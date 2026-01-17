<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Port;

use App\Core\DataExchange\Exception\ImportValidationException;

/**
 * Port interface for parsing import data from specific formats.
 */
interface ImportParser
{
    /**
     * Returns the format identifier this parser handles.
     */
    public function getFormat(): string;

    /**
     * Parse from file path for streaming large files.
     *
     * @return iterable<array<string, string>>
     */
    public function parseFile(string $filePath): iterable;

    /**
     * Parse from string content.
     *
     * @return iterable<array<string, string>>
     */
    public function parse(string $content): iterable;

    /**
     * Validate headers match expected structure.
     *
     * @param string[] $expectedHeaders
     * @param string[] $actualHeaders
     *
     * @throws ImportValidationException
     */
    public function validateHeaders(array $expectedHeaders, array $actualHeaders): void;
}
