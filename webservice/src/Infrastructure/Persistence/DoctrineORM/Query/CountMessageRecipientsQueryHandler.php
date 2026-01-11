<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Query\CountOfMessageRecipients;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CountMessageRecipientsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountOfMessageRecipients $query): int
    {
        $dql = 'SELECT COUNT(r.id) FROM '.AbstractMessageRecipient::class.' r';
        $parameters = [];
        $whereClauses = [];

        if (null !== $query->filterType) {
            $whereClauses[] = 'r INSTANCE OF :type';
            $parameters['type'] = $this->em->getClassMetadata($query->filterType);
        }

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $whereClauses[] = 'LOWER(r.name) LIKE LOWER(:textFilter)';
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
