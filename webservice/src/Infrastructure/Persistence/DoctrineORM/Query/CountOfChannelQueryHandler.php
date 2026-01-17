<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Query\CountOfChannel;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class CountOfChannelQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountOfChannel $query): int
    {
        $dql = sprintf(
            'SELECT COUNT(c.id) FROM %s c',
            Channel::class
        );
        $parameters = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $dql .= ' WHERE c.name LIKE :query';
            $parameters['query'] = '%'.$query->textFilter.'%';
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
