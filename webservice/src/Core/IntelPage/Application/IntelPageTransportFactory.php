<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Application;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\EventBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportContract\Model\SystemTransportConfig;

/**
 * The factory combines services from dependency injection with the individual configuration.
 */
final readonly class IntelPageTransportFactory implements \App\Core\TransportContract\Port\TransportFactory\IntelPageTransportFactory
{
    public function __construct(
        private QueryBus $queryBus,
        private CommandBus $commandBus,
        private EventBus $eventBus,
    ) {
    }

    public function withSystemConfiguration(SystemTransportConfig $config): IntelPageTransport
    {
        return new IntelPageTransport(
            $config,
            $this->queryBus,
            $this->commandBus,
            $this->eventBus,
        );
    }
}
