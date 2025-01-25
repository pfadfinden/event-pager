<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\AllTransports;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AllTransportsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<TransportConfiguration>
     */
    public function __invoke(AllTransports $query): iterable
    {
        $dql = 'SELECT t FROM '.TransportConfiguration::class.' t';
        $parameters = [];

        if (null !== $query->filterEnabledStatus) {
            $dql .= ' WHERE t.enabled = :enabled';
            $parameters['enabled'] = $query->filterEnabledStatus;
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return $doctrineQuery->toIterable();
    }
}
