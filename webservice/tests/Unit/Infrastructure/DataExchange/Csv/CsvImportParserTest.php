<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DataExchange\Csv;

use App\Core\DataExchange\Exception\ImportValidationException;
use App\Infrastructure\DataExchange\Csv\CsvImportParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvImportParser::class)]
final class CsvImportParserTest extends TestCase
{
    private CsvImportParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CsvImportParser();
    }

    public function testGetFormatReturnsCsv(): void
    {
        self::assertSame('csv', $this->parser->getFormat());
    }

    public function testParseReturnsIterableFromCsvContent(): void
    {
        $csv = "id,name,value\n1,test,100\n2,example,200";

        $rows = iterator_to_array($this->parser->parse($csv), false);

        self::assertCount(2, $rows);
        self::assertSame(['id' => '1', 'name' => 'test', 'value' => '100'], $rows[0]);
        self::assertSame(['id' => '2', 'name' => 'example', 'value' => '200'], $rows[1]);
    }

    public function testParseHandlesEmptyContent(): void
    {
        $csv = "id,name\n";

        $rows = iterator_to_array($this->parser->parse($csv), false);

        self::assertCount(0, $rows);
    }

    public function testParseHandlesSpecialCharacters(): void
    {
        $csv = "id,name,description\n1,\"Test, with comma\",\"Line1\nLine2\"";

        $rows = iterator_to_array($this->parser->parse($csv), false);

        self::assertCount(1, $rows);
        self::assertSame('Test, with comma', $rows[0]['name']);
        self::assertSame("Line1\nLine2", $rows[0]['description']);
    }

    public function testParseFileReturnsIterableFromFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, "id,name\n1,test\n2,example");

        try {
            $rows = iterator_to_array($this->parser->parseFile($tempFile), false);

            self::assertCount(2, $rows);
            self::assertSame(['id' => '1', 'name' => 'test'], $rows[0]);
            self::assertSame(['id' => '2', 'name' => 'example'], $rows[1]);
        } finally {
            unlink($tempFile);
        }
    }

    public function testValidateHeadersPassesWhenAllHeadersPresent(): void
    {
        $expected = ['id', 'name', 'value'];
        $actual = ['id', 'name', 'value', 'extra'];

        // Should not throw - if we reach here without exception, test passes
        $this->parser->validateHeaders($expected, $actual);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateHeadersThrowsWhenHeadersMissing(): void
    {
        $expected = ['id', 'name', 'value'];
        $actual = ['id', 'name'];

        self::expectException(ImportValidationException::class);
        self::expectExceptionMessage('Missing required columns: value');

        $this->parser->validateHeaders($expected, $actual);
    }

    public function testValidateHeadersThrowsWithMultipleMissingHeaders(): void
    {
        $expected = ['id', 'name', 'value', 'description'];
        $actual = ['id'];

        self::expectException(ImportValidationException::class);
        self::expectExceptionMessage('name');
        self::expectExceptionMessage('value');

        $this->parser->validateHeaders($expected, $actual);
    }
}
