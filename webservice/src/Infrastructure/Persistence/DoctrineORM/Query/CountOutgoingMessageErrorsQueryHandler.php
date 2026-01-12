<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Query\CountOutgoingMessageErrors;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CountOutgoingMessageErrorsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountOutgoingMessageErrors $query): int
    {
        $dql = 'SELECT COUNT(e.id) FROM '.OutgoingMessageEventRecord::class.' e WHERE e.status IN (:statuses)';
        $parameters = [
            'statuses' => [OutgoingMessageStatus::ERROR->value, OutgoingMessageStatus::TIMEOUT->value],
        ];

        if (null !== $query->since) {
            $dql .= ' AND e.recordedAt >= :since';
            $parameters['since'] = $query->since;
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
