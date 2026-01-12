<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\CountUsers;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CountUsersQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountUsers $query): int
    {
        $dql = 'SELECT COUNT(u.id) FROM '.User::class.' u';
        $parameters = [];
        $whereClauses = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $whereClauses[] = '(LOWER(u.username) LIKE LOWER(:textFilter) OR LOWER(u.displayname) LIKE LOWER(:textFilter))';
            $parameters['textFilter'] = '%'.$query->textFilter.'%';
        }

        if ([] !== $whereClauses) {
            $dql .= ' WHERE '.implode(' AND ', $whereClauses);
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
