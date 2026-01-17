<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Port\TransportConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransportConfigurationRepository implements TransportConfigurationRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(TransportConfiguration $transportConfiguration): void
    {
        $this->entityManager->persist($transportConfiguration);
    }

    public function removeByKey(string $key): void
    {
        $transportConfiguration = $this->getByKey($key);
        if (!$transportConfiguration instanceof TransportConfiguration) {
            return;
        }
        $this->entityManager->remove($transportConfiguration);
    }

    public function getByKey(string $key): ?TransportConfiguration
    {
        return $this->entityManager->find(TransportConfiguration::class, $key);
    }
}
