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
            'SELECT NEW %s(p.id, p.label, p.number) FROM %s p',
            Pager::class,
            \App\Core\IntelPage\Model\Pager::class
        );

        $doctrineQuery = $this->em->createQuery($dql);

        return $doctrineQuery->toIterable();
    }
}
