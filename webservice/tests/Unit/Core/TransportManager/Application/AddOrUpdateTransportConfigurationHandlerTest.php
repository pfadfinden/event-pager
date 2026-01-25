<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\TransportManager\Application;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\TransportManager\Application\AddOrUpdateTransportConfigurationHandler;
use App\Core\TransportManager\Command\AddOrUpdateTransportConfiguration;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use App\Tests\Mock\DummyTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddOrUpdateTransportConfiguration::class)]
#[CoversClass(AddOrUpdateTransportConfigurationHandler::class)]
final class AddOrUpdateTransportConfigurationHandlerTest extends TestCase
{
    public function testCanAddConfiguration(): void
    {
        $vendorSpecificConfiguration = ['new' => 'hey'];

        $repository = self::createMock(TransportConfigurationRepository::class);
        $repository->expects(self::once())->method('persist')
            ->with(self::callback(fn ($value): bool => $value instanceof TransportConfiguration
                && 'test-dummy' === $value->getKey()
                && DummyTransport::class === $value->getTransport()
                && 'Hello World' === $value->getTitle()
                && $value->isEnabled()
                && $vendorSpecificConfiguration === $value->getVendorSpecificConfig()));

        $uow = self::createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddOrUpdateTransportConfigurationHandler($repository, $uow);

        $command = AddOrUpdateTransportConfiguration::with(
            'test-dummy',
            DummyTransport::class,
            'Hello World',
            true,
            $vendorSpecificConfiguration
        );
        $sut->__invoke($command);
    }

    public function testCanUpdateConfiguration(): void
    {
        $transportConfiguration = new TransportConfiguration('test-dummy',
            '\App\Tests\Mock\OldDummyTransport',
            'Hello App'
        );
        $repository = self::createMock(TransportConfigurationRepository::class);
        $repository->expects(self::once())->method('getByKey')
            ->with('test-dummy')
            ->willReturn($transportConfiguration);
        $repository->expects(self::once())->method('persist')
            ->with(self::callback(fn ($value): bool => $value === $transportConfiguration
                && 'test-dummy' === $value->getKey()
                && DummyTransport::class === $value->getTransport()
                && 'Hello World' === $value->getTitle()));
        $uow = self::createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new AddOrUpdateTransportConfigurationHandler($repository, $uow);

        $command = AddOrUpdateTransportConfiguration::with(
            'test-dummy',
            DummyTransport::class,
            'Hello World'
        );
        $sut->__invoke($command);
    }
}
