<?php

declare(strict_types=1);

namespace App\Tests\Core\TelegramTransport\Application;

use App\Core\Contracts\Bus\EventBus;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Core\TelegramTransport\Application\TelegramTransportFactory;
use App\Core\TelegramTransport\Port\TelegramClientInterface;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(TelegramTransportFactory::class)]
#[Group('unit')]
final class TelegramTransportFactoryTest extends TestCase
{
    public function testSupportsReturnsTrueForTelegramTransport(): void
    {
        $factory = new TelegramTransportFactory(
            self::createStub(TelegramClientInterface::class),
            self::createStub(EventBus::class),
        );

        self::assertTrue($factory->supports(TelegramTransport::class));
    }

    public function testSupportsReturnsFalseForOtherTransports(): void
    {
        $factory = new TelegramTransportFactory(
            self::createStub(TelegramClientInterface::class),
            self::createStub(EventBus::class),
        );

        self::assertFalse($factory->supports(Transport::class));
    }

    public function testWithSystemConfigurationReturnsTelegramTransport(): void
    {
        $factory = new TelegramTransportFactory(
            self::createStub(TelegramClientInterface::class),
            self::createStub(EventBus::class),
        );

        $configMock = self::createMock(SystemTransportConfig::class);
        $configMock->method('getKey')->willReturn('test-telegram');

        $transport = $factory->withSystemConfiguration($configMock);

        self::assertSame('test-telegram', $transport->key());
    }
}
