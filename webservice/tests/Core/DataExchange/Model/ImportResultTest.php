<?php

declare(strict_types=1);

namespace App\Tests\Core\DataExchange\Model;

use App\Core\DataExchange\Model\ImportResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImportResult::class)]
#[Group('unit')]
final class ImportResultTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $result = new ImportResult(
            imported: 10,
            updated: 5,
            skippedCount: 3,
            errors: ['Error 1', 'Error 2'],
            skipped: ['id1', 'id2', 'id3'],
        );

        self::assertSame(10, $result->imported);
        self::assertSame(5, $result->updated);
        self::assertSame(3, $result->skippedCount);
        self::assertSame(['Error 1', 'Error 2'], $result->errors);
        self::assertSame(['id1', 'id2', 'id3'], $result->skipped);
    }

    public function testHasErrorsReturnsTrueWhenErrorsExist(): void
    {
        $result = new ImportResult(
            imported: 0,
            updated: 0,
            skippedCount: 0,
            errors: ['Some error occurred'],
            skipped: [],
        );

        self::assertTrue($result->hasErrors());
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $result = new ImportResult(
            imported: 10,
            updated: 0,
            skippedCount: 0,
            errors: [],
            skipped: [],
        );

        self::assertFalse($result->hasErrors());
    }

    public function testTotalProcessedReturnsSum(): void
    {
        $result = new ImportResult(
            imported: 10,
            updated: 5,
            skippedCount: 3,
            errors: [],
            skipped: [],
        );

        self::assertSame(18, $result->totalProcessed());
    }

    public function testTotalProcessedWithZeroValues(): void
    {
        $result = new ImportResult(
            imported: 0,
            updated: 0,
            skippedCount: 0,
            errors: [],
            skipped: [],
        );

        self::assertSame(0, $result->totalProcessed());
    }
}
