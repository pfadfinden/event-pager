<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\DataExchange\ReadModel;

use App\Core\DataExchange\ReadModel\ChannelExportRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChannelExportRow::class)]
final class ChannelExportRowTest extends TestCase
{
    public function testCsvHeadersReturnsCorrectHeaders(): void
    {
        $headers = ChannelExportRow::csvHeaders();

        self::assertSame(['id', 'name', 'cap_code', 'audible', 'vibration'], $headers);
    }

    public function testToArrayWithAudibleAndVibrationTrue(): void
    {
        $row = new ChannelExportRow(
            id: '01HTEST123456789ABCDEFGH',
            name: 'Test Channel',
            capCode: 1234,
            audible: true,
            vibration: true,
        );

        $array = $row->toArray();

        self::assertSame('01HTEST123456789ABCDEFGH', $array['id']);
        self::assertSame('Test Channel', $array['name']);
        self::assertSame(1234, $array['cap_code']);
        self::assertSame('1', $array['audible']);
        self::assertSame('1', $array['vibration']);
    }

    public function testToArrayWithAudibleAndVibrationFalse(): void
    {
        $row = new ChannelExportRow(
            id: '01HTEST123456789ABCDEFGH',
            name: 'Silent Channel',
            capCode: 5678,
            audible: false,
            vibration: false,
        );

        $array = $row->toArray();

        self::assertSame('0', $array['audible']);
        self::assertSame('0', $array['vibration']);
    }

    public function testToArrayKeysMatchCsvHeaders(): void
    {
        $row = new ChannelExportRow(
            id: '01HTEST123456789ABCDEFGH',
            name: 'Test',
            capCode: 1000,
            audible: true,
            vibration: true,
        );

        $headers = ChannelExportRow::csvHeaders();
        $arrayKeys = array_keys($row->toArray());

        self::assertSame($headers, $arrayKeys);
    }
}
