<?php

declare(strict_types=1);

namespace App\Tests\Core\TransportManager\Application;

use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\TransportManager\Application\RemoveTransportConfigurationHandler;
use App\Core\TransportManager\Command\RemoveTransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveTransportConfiguration::class)]
#[CoversClass(RemoveTransportConfigurationHandler::class)]
#[Group('unit')]
final class RemoveTransportConfigurationHandlerTest extends TestCase
{
    public function testCanRemoveConfiguration(): void
    {
        $repository = self::createMock(TransportConfigurationRepository::class);
        $repository->expects(self::once())->method('removeByKey')
            ->with('test-dummy');
        $uow = self::createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('commit');

        $sut = new RemoveTransportConfigurationHandler($repository, $uow);

        $command = new RemoveTransportConfiguration(
            'test-dummy',
        );
        $sut->__invoke($command);
    }
}
