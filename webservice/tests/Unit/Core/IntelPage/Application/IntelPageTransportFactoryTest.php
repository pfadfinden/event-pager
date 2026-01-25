<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Application\IntelPageTransportFactory;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntelPageTransportFactory::class)]
final class IntelPageTransportFactoryTest extends TestCase
{
    public function testCanInstantiateTransport(): void
    {
        // Arrange
        $queryBus = self::createStub(QueryBus::class);
        $commandBus = self::createStub(CommandBus::class);
        $eventBus = self::createStub(EventBus::class);

        $factory = new IntelPageTransportFactory(
            $queryBus,
            $commandBus,
            $eventBus,
        );

        $systemTransportConfig = self::createStub(SystemTransportConfig::class);
        $systemTransportConfig->method('getKey')->willReturn('some_key');

        // Act
        $transport = $factory->withSystemConfiguration($systemTransportConfig);

        // Assert
        self::assertSame('some_key', $transport->key());
    }
}
