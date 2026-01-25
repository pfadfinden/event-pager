<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TransportManager\Model;

use App\Core\TransportManager\Model\TransportConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransportConfiguration::class)]
final class TransportConfigurationTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $sut = new TransportConfiguration(
            'test-dummy',
            '\App\Tests\Mock\DummyTransport',
            'Hello World'
        );

        self::assertSame('test-dummy', $sut->getKey());
        self::assertSame('\App\Tests\Mock\DummyTransport', $sut->getTransport());
        self::assertSame('Hello World', $sut->getTitle());
        self::assertFalse($sut->isEnabled(), 'per default transports are expected to be disabled');
        self::assertNull($sut->getVendorSpecificConfig());
    }

    public function testCanBeModified(): void
    {
        $sut = new TransportConfiguration(
            'test-dummy',
            '\App\Tests\Mock\DummyTransport',
            'Hello World'
        );

        $sut->setTitle('Test Title');
        $sut->setTransport('\App\Tests\Mock\DummyTransport2'); // @phpstan-ignore argument.type
        $sut->setVendorSpecificConfig(['config' => 'Test Vendor Specific Config']);
        $sut->setEnabled(true);

        self::assertSame('test-dummy', $sut->getKey());
        self::assertSame('\App\Tests\Mock\DummyTransport2', $sut->getTransport());
        self::assertSame('Test Title', $sut->getTitle());
        self::assertTrue($sut->isEnabled(), 'per default transports are expected to be disabled');
        self::assertSame(['config' => 'Test Vendor Specific Config'], $sut->getVendorSpecificConfig());
    }
}
