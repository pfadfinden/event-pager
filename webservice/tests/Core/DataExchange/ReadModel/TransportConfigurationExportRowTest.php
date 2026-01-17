<?php

declare(strict_types=1);

namespace App\Tests\Core\DataExchange\ReadModel;

use App\Core\DataExchange\ReadModel\TransportConfigurationExportRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransportConfigurationExportRow::class)]
#[Group('unit')]
final class TransportConfigurationExportRowTest extends TestCase
{
    public function testCsvHeadersReturnsCorrectHeaders(): void
    {
        $headers = TransportConfigurationExportRow::csvHeaders();

        self::assertSame(
            ['key', 'transport', 'title', 'enabled', 'vendor_specific_config'],
            $headers,
        );
    }

    public function testToArrayWithAllFieldsPopulated(): void
    {
        $configJson = '{"serverUrl":"https://example.com"}';
        $row = new TransportConfigurationExportRow(
            key: 'ntfy-prod',
            transport: 'App\\Core\\NtfyTransport\\Application\\NtfyTransport',
            title: 'NTFY Production',
            enabled: true,
            vendorSpecificConfig: $configJson,
        );

        $array = $row->toArray();

        self::assertSame('ntfy-prod', $array['key']);
        self::assertSame('App\\Core\\NtfyTransport\\Application\\NtfyTransport', $array['transport']);
        self::assertSame('NTFY Production', $array['title']);
        self::assertSame('1', $array['enabled']);
        self::assertSame($configJson, $array['vendor_specific_config']);
    }

    public function testToArrayWithDisabledAndNullConfig(): void
    {
        $row = new TransportConfigurationExportRow(
            key: 'telegram-dev',
            transport: 'App\\Core\\TelegramTransport\\Application\\TelegramTransport',
            title: 'Telegram Dev',
            enabled: false,
            vendorSpecificConfig: null,
        );

        $array = $row->toArray();

        self::assertSame('0', $array['enabled']);
        self::assertSame('', $array['vendor_specific_config']);
    }

    public function testToArrayKeysMatchCsvHeaders(): void
    {
        $row = new TransportConfigurationExportRow(
            key: 'test',
            transport: 'TestTransport',
            title: 'Test',
            enabled: true,
            vendorSpecificConfig: null,
        );

        $headers = TransportConfigurationExportRow::csvHeaders();
        $arrayKeys = array_keys($row->toArray());

        self::assertSame($headers, $arrayKeys);
    }
}
