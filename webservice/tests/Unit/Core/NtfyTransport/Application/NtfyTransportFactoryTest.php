<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\NtfyTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\NtfyTransport\Application\NtfyTransportFactory;
use App\Core\NtfyTransport\Port\NtfyClientInterface;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NtfyTransportFactory::class)]
#[AllowMockObjectsWithoutExpectations]
final class NtfyTransportFactoryTest extends TestCase
{
    public function testSupportsReturnsTrueForNtfyTransport(): void
    {
        $factory = new NtfyTransportFactory(
            self::createMock(NtfyClientInterface::class),
            self::createMock(EventBus::class),
        );

        self::assertTrue($factory->supports(NtfyTransport::class));
    }

    public function testSupportsReturnsFalseForOtherTransports(): void
    {
        $factory = new NtfyTransportFactory(
            self::createMock(NtfyClientInterface::class),
            self::createMock(EventBus::class),
        );

        self::assertFalse($factory->supports(IntelPageTransport::class));
    }

    public function testWithSystemConfigurationReturnsNtfyTransport(): void
    {
        $factory = new NtfyTransportFactory(
            self::createMock(NtfyClientInterface::class),
            self::createMock(EventBus::class),
        );

        $configMock = self::createMock(SystemTransportConfig::class);
        $configMock->method('getKey')->willReturn('test-ntfy');

        $transport = $factory->withSystemConfiguration($configMock);

        self::assertSame('test-ntfy', $transport->key());
    }
}
