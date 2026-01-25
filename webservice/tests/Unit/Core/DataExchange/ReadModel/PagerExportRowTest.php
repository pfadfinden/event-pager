<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\DataExchange\ReadModel;

use App\Core\DataExchange\ReadModel\PagerExportRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PagerExportRow::class)]
final class PagerExportRowTest extends TestCase
{
    public function testCsvHeadersReturnsCorrectHeaders(): void
    {
        $headers = PagerExportRow::csvHeaders();

        self::assertSame(
            ['id', 'label', 'number', 'comment', 'activated', 'carried_by_id', 'slot_assignments'],
            $headers,
        );
    }

    public function testToArrayWithAllFieldsPopulated(): void
    {
        $slotJson = '[{"slot":0,"type":"individual","capCode":1234}]';
        $row = new PagerExportRow(
            id: '01HTEST123456789ABCDEFGH',
            label: 'Security Lead',
            number: 42,
            comment: 'Assigned to main entrance',
            activated: true,
            carriedById: '01HCARRIER12345678ABCDEF',
            slotAssignments: $slotJson,
        );

        $array = $row->toArray();

        self::assertSame('01HTEST123456789ABCDEFGH', $array['id']);
        self::assertSame('Security Lead', $array['label']);
        self::assertSame(42, $array['number']);
        self::assertSame('Assigned to main entrance', $array['comment']);
        self::assertSame('1', $array['activated']);
        self::assertSame('01HCARRIER12345678ABCDEF', $array['carried_by_id']);
        self::assertSame($slotJson, $array['slot_assignments']);
    }

    public function testToArrayWithNullValuesConvertsToEmptyStrings(): void
    {
        $row = new PagerExportRow(
            id: '01HTEST123456789ABCDEFGH',
            label: 'Spare Pager',
            number: 99,
            comment: null,
            activated: false,
            carriedById: null,
            slotAssignments: null,
        );

        $array = $row->toArray();

        self::assertSame('', $array['comment']);
        self::assertSame('0', $array['activated']);
        self::assertSame('', $array['carried_by_id']);
        self::assertSame('', $array['slot_assignments']);
    }

    public function testToArrayKeysMatchCsvHeaders(): void
    {
        $row = new PagerExportRow(
            id: '01HTEST123456789ABCDEFGH',
            label: 'Test',
            number: 1,
            comment: null,
            activated: true,
            carriedById: null,
            slotAssignments: null,
        );

        $headers = PagerExportRow::csvHeaders();
        $arrayKeys = array_keys($row->toArray());

        self::assertSame($headers, $arrayKeys);
    }
}
