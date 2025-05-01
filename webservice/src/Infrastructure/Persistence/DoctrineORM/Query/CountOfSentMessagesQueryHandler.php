<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\CountOfSendMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function count;
use function sprintf;

final readonly class CountOfSentMessagesQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(CountOfSendMessages $query): int
    {
        $dql = sprintf('SELECT COUNT(m.messageId) FROM %s m', IncomingMessage::class);
        $dqlWhereClauses = [];
        $parameters = [];

        if (null !== $query->sendBy) {
            $dqlWhereClauses[] = 'm.by = :sentBy';
            // Doctrine currently uses UUID format to store ULIDs, therefore we have to convert here:
            $parameters['sentBy'] = Ulid::fromString($query->sendBy)->toRfc4122();
        }

        if (null !== $query->since) {
            $dqlWhereClauses[] = 'm.sentOn >= :sentSince';
            // Doctrine currently uses UUID format to store ULIDs, therefore we have to convert here:
            $parameters['sentSince'] = $query->since;
        }

        if (count($dqlWhereClauses) > 0) {
            $dql .= ' WHERE '.implode(' AND ', $dqlWhereClauses);
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
