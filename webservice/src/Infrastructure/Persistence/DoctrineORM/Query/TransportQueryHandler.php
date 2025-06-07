<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\Transport;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransportQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(Transport $query): ?TransportConfiguration
    {
        return $this->em->find(TransportConfiguration::class, $query->key);
    }
}
