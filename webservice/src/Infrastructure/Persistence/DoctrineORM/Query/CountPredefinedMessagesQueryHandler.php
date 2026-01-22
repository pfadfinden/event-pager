<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Query\CountPredefinedMessages;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CountPredefinedMessagesQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountPredefinedMessages $query): int
    {
        $dql = 'SELECT COUNT(p.id) FROM '.PredefinedMessage::class.' p';
        $parameters = [];
        $whereClauses = [];

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $whereClauses[] = '(LOWER(p.title) LIKE LOWER(:textFilter) OR LOWER(p.messageContent) LIKE LOWER(:textFilter))';
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
