<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Query\AllPager;
use App\Core\IntelPage\ReadModel\Pager;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class AllPagerQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<Pager>
     */
    public function __invoke(AllPager $query): iterable
    {
        $dql = sprintf(
            'SELECT NEW %s(p.id, p.label, p.number, p.comment, p.activated, c.id, c.name) FROM %s p LEFT JOIN p.carriedBy c',
            Pager::class,
            \App\Core\IntelPage\Model\Pager::class
        );
        $parameters = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $dql .= ' WHERE p.label LIKE :query OR c.name LIKE :query';
            $parameters['query'] = '%'.$query->textFilter.'%';
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        if ($query->page >= 1 || null !== $query->perPage) {
            $limit = $query->perPage ?? AllPager::DEFAULT_PAGE_LENGTH;
            $doctrineQuery->setMaxResults($limit);
            $doctrineQuery->setFirstResult(max(((int) $query->page - 1) * $limit, 0));
        }

        return $doctrineQuery->toIterable();
    }
}
