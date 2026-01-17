<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\TransportManager\Command\AddOrUpdateTransportConfiguration;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AddOrUpdateTransportConfigurationHandler
{
    public function __construct(
        private TransportConfigurationRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AddOrUpdateTransportConfiguration $command): void
    {
        $configuration = $this->repository->getByKey($command->key);

        if (!$configuration instanceof TransportConfiguration) {
            $configuration = new TransportConfiguration(
                $command->key,
                $command->transport,
                $command->title,
            );
        } else {
            $configuration->setTransport($command->transport);
            $configuration->setTitle($command->title);
        }

        $configuration->setEnabled($command->enabled ?? false);
        $configuration->setVendorSpecificConfig($command->vendorSpecificConfiguration);

        $this->repository->persist($configuration);
        $this->uow->commit();
    }
}
