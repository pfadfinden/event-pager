<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\TransportManager\Command\RemoveTransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class RemoveTransportConfigurationHandler
{
    public function __construct(
        private TransportConfigurationRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(RemoveTransportConfiguration $command): void
    {
        $this->repository->removeByKey($command->key);
        $this->uow->commit();
    }
}
