<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Query\CountOfPager;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class CountOfPagerQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountOfPager $query): int
    {
        $dql = sprintf(
            'SELECT COUNT(p.id) FROM %s p',
            Pager::class
        );
        $parameters = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $dql .= ' LEFT JOIN p.carriedBy c WHERE p.label LIKE :query OR c.name LIKE :query';
            $parameters['query'] = '%'.$query->textFilter.'%';
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
