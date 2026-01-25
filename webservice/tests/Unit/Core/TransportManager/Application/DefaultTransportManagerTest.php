<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TransportManager\Application;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportContract\Model\SystemTransportConfig;
use App\Core\TransportContract\Port\Transport;
use App\Core\TransportContract\Port\TransportFactory;
use App\Core\TransportManager\Application\DefaultTransportManager;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\AllTransports;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultTransportManager::class)]
final class DefaultTransportManagerTest extends TestCase
{
    public function testDeliversInstancesOfAllEnabledTransports(): void
    {
        // Arrange
        $queryBusMock = $this->createMock(QueryBus::class);
        $transportConfiguration1 = new TransportConfiguration('test01', 'testClass', 'Dummy 01');
        $transportConfiguration2 = new TransportConfiguration('test02', 'otherTestClass', 'Dummy 02');
        $queryBusMock->expects($this->once())
            ->method('get')
            ->with(self::isInstanceOf(AllTransports::class))
            ->willReturn([
                $transportConfiguration1,
                $transportConfiguration2,
            ]);

        $transportFactories = [
            // other not enabled transport, skipped over
            $this->mockTransportFactory([['testClass', false], ['otherTestClass', false]]),
            // does not accept new messages
            $this->mockTransportFactory([['testClass', false], ['otherTestClass', true]]),
            // active transport:
            $this->mockTransportFactory([['testClass', true], ['otherTestClass', false]], $transportConfiguration1),
        ];

        $sut = new DefaultTransportManager($queryBusMock, $transportFactories);

        // Act
        $transports = iterator_to_array($sut->activeTransports());

        // Assert
        self::assertCount(1, $transports);
        self::assertContainsOnlyInstancesOf(Transport::class, $transports);
    }

    public function testActiveTransportsDoesNotThrowIfNoFactoryFound(): void
    {
        // Arrange
        $queryBusMock = $this->createMock(QueryBus::class);
        $transportConfiguration2 = new TransportConfiguration('test02', 'otherTestClass', 'Dummy 02');
        $queryBusMock->expects($this->once())
            ->method('get')
            ->with(self::isInstanceOf(AllTransports::class))
            ->willReturn([
                $transportConfiguration2,
            ]);

        $transportFactories = [
            // other not enabled transport, skipped over
            $this->mockTransportFactory([['testClass', false], ['otherTestClass', false]]),
        ];

        $sut = new DefaultTransportManager($queryBusMock, $transportFactories);

        // Act
        $transports = iterator_to_array($sut->activeTransports());

        // Assert
        self::assertCount(0, $transports);
    }

    public function testActiveTransportsDoesNotThrowIfNoTransportsAcceptNewMessages(): void
    {
        // Arrange
        $queryBusMock = $this->createMock(QueryBus::class);
        $transportConfiguration2 = new TransportConfiguration('test02', 'otherTestClass', 'Dummy 02');
        $queryBusMock->expects($this->once())
            ->method('get')
            ->with(self::isInstanceOf(AllTransports::class))
            ->willReturn([
                $transportConfiguration2,
            ]);

        $transportFactories = [
            // other not enabled transport, skipped over
            $this->mockTransportFactory([['testClass', false], ['otherTestClass', false]]),
            // does not accept new messages
            $this->mockTransportFactory([['testClass', false], ['otherTestClass', true]]),
        ];

        $sut = new DefaultTransportManager($queryBusMock, $transportFactories);

        // Act
        $transports = iterator_to_array($sut->activeTransports());

        // Assert
        self::assertCount(0, $transports);
    }

    /**
     * @param array<int, string[]>|array<int, bool[]> $supportsMap
     */
    public function mockTransportFactory(array $supportsMap, ?SystemTransportConfig $config = null): TransportFactory
    {
        $factory = self::createStub(TransportFactory::class);
        $factory->method('supports')->willReturnMap($supportsMap);
        $mockTransport = self::createStub(Transport::class);
        $mockTransport->method('acceptsNewMessages')->willReturn($config instanceof SystemTransportConfig);
        $factory->method('withSystemConfiguration')
            ->with()->willReturn($mockTransport);

        return $factory;
    }
}
