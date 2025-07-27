<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Query\Pager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class PagerQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(Pager $query): \App\Core\IntelPage\ReadModel\Pager
    {
        $dql = sprintf(
            'SELECT NEW %s(p.id, p.label, p.number, p.activated, r.id, r.name) FROM %s p LEFT JOIN p.carriedBy as r WHERE p.id = :pagerId',
            \App\Core\IntelPage\ReadModel\Pager::class,
            \App\Core\IntelPage\Model\Pager::class
        );

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters(['pagerId' => Ulid::fromString($query->id)->toRfc4122()]);

        // @phpstan-ignore return.type
        return $doctrineQuery->getSingleResult();
    }
}
