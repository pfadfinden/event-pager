<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\CountMessageHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class CountMessageHistoryQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountMessageHistory $query): int
    {
        $filter = $query->filter;
        $source = IncomingMessage::class;

        $dql = "SELECT COUNT(m.messageId) FROM $source m";
        $parameters = [];
        $whereClauses = [];

        if (null !== $filter->sentByUserId && '' !== $filter->sentByUserId) {
            $whereClauses[] = 'm.by = :sentBy';
            $parameters['sentBy'] = Ulid::fromString($filter->sentByUserId)->toRfc4122();
        }

        if (null !== $filter->searchText && '' !== $filter->searchText) {
            $whereClauses[] = 'LOWER(m.content) LIKE LOWER(:searchText)';
            $parameters['searchText'] = '%'.$filter->searchText.'%';
        }

        if ([] !== $whereClauses) {
            $dql .= ' WHERE '.implode(' AND ', $whereClauses);
        }

        return (int) $this->em->createQuery($dql)
            ->setParameters($parameters)
            ->getSingleScalarResult();
    }
}
