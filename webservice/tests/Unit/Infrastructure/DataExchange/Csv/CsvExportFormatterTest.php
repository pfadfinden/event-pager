<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DataExchange\Csv;

use App\Core\DataExchange\ReadModel\ChannelExportRow;
use App\Infrastructure\DataExchange\Csv\CsvExportFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvExportFormatter::class)]
final class CsvExportFormatterTest extends TestCase
{
    private CsvExportFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new CsvExportFormatter();
    }

    public function testGetFormatReturnsCsv(): void
    {
        self::assertSame('csv', $this->formatter->getFormat());
    }

    public function testGetContentTypeReturnsTextCsv(): void
    {
        self::assertSame('text/csv; charset=UTF-8', $this->formatter->getContentType());
    }

    public function testGetFileExtensionReturnsCsvExtension(): void
    {
        self::assertSame('.csv', $this->formatter->getFileExtension());
    }

    public function testFormatStreamingOutputsHeadersFirst(): void
    {
        $rows = [];
        $headers = ['id', 'name', 'value'];

        $output = iterator_to_array($this->formatter->formatStreaming($rows, $headers));

        self::assertNotEmpty($output);
        self::assertStringContainsString('id', $output[0]);
        self::assertStringContainsString('name', $output[0]);
        self::assertStringContainsString('value', $output[0]);
    }

    public function testFormatStreamingOutputsRowsAfterHeaders(): void
    {
        $rows = [
            new ChannelExportRow('01HTEST1', 'Channel 1', 1001, true, false),
            new ChannelExportRow('01HTEST2', 'Channel 2', 1002, false, true),
        ];
        $headers = ChannelExportRow::csvHeaders();

        $output = iterator_to_array($this->formatter->formatStreaming($rows, $headers));

        // First chunk is headers, next chunks are rows
        self::assertCount(3, $output);

        // Verify header row
        self::assertStringContainsString('id', $output[0]);
        self::assertStringContainsString('name', $output[0]);

        // Verify data rows
        self::assertStringContainsString('01HTEST1', $output[1]);
        self::assertStringContainsString('Channel 1', $output[1]);
        self::assertStringContainsString('1001', $output[1]);

        self::assertStringContainsString('01HTEST2', $output[2]);
        self::assertStringContainsString('Channel 2', $output[2]);
    }

    public function testFormatStreamingWithEmptyRows(): void
    {
        $rows = [];
        $headers = ['id', 'name'];

        $output = iterator_to_array($this->formatter->formatStreaming($rows, $headers));

        // Should only have headers
        self::assertCount(1, $output);
    }

    public function testFormatStreamingHandlesBooleanConversion(): void
    {
        $rows = [
            new ChannelExportRow('01HTEST1', 'Audible', 1001, true, true),
            new ChannelExportRow('01HTEST2', 'Silent', 1002, false, false),
        ];
        $headers = ChannelExportRow::csvHeaders();

        $output = iterator_to_array($this->formatter->formatStreaming($rows, $headers));

        // Check audible=true, vibration=true → '1', '1'
        self::assertStringContainsString('1', $output[1]);

        // Check audible=false, vibration=false → '0', '0'
        self::assertStringContainsString('0', $output[2]);
    }

    public function testFormatStreamingYieldsIncrementally(): void
    {
        $rows = [
            new ChannelExportRow('01HTEST1', 'Test 1', 1001, true, true),
            new ChannelExportRow('01HTEST2', 'Test 2', 1002, false, false),
        ];
        $headers = ChannelExportRow::csvHeaders();

        $generator = $this->formatter->formatStreaming($rows, $headers);

        // First yield should be headers
        $firstChunk = $generator->current();
        self::assertStringContainsString('id', $firstChunk);

        // Advance to first data row
        $generator->next();
        $secondChunk = $generator->current();
        self::assertStringContainsString('01HTEST1', $secondChunk);
    }
}
