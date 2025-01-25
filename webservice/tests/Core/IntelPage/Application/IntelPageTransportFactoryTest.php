<?php

declare(strict_types=1);

namespace App\Tests\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Application\IntelPageTransportFactory;
use App\Core\TransportContract\Model\SystemTransportConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntelPageTransportFactory::class)]
#[Group('unit')]
final class IntelPageTransportFactoryTest extends TestCase
{
    public function testCanInstantiateTransport(): void
    {
        // Arrange
        $queryBus = self::createMock(QueryBus::class);
        $commandBus = self::createMock(CommandBus::class);
        $eventBus = self::createMock(EventBus::class);

        $factory = new IntelPageTransportFactory(
            $queryBus,
            $commandBus,
            $eventBus,
        );

        $systemTransportConfig = self::createMock(SystemTransportConfig::class);
        $systemTransportConfig->method('getKey')->willReturn('some_key');

        // Act
        $transport = $factory->withSystemConfiguration($systemTransportConfig);

        // Assert
        self::assertSame('some_key', $transport->key());
    }
}
