<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Query\ListMessageHistory;
use App\Core\SendMessage\ReadModel\MessageHistoryEntry;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class ListMessageHistoryQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<MessageHistoryEntry>
     */
    public function __invoke(ListMessageHistory $query): iterable
    {
        $incomingMessages = $this->fetchIncomingMessages($query);

        foreach ($incomingMessages as $message) {
            $messageId = $message->messageId;
            $statusCounts = $this->fetchStatusCounts($messageId);
            $totalOutgoing = array_sum($statusCounts);

            yield new MessageHistoryEntry(
                $messageId->toString(),
                $message->sentOn->toISOString(),
                $message->by->toString(),
                $message->content,
                $message->priority,
                $statusCounts,
                $totalOutgoing,
                array_map(fn (Ulid $ulid) => $ulid->toString(), $message->to),
            );
        }
    }

    /**
     * @return iterable<IncomingMessage>
     */
    private function fetchIncomingMessages(ListMessageHistory $query): iterable
    {
        $filter = $query->filter;
        $source = IncomingMessage::class;

        $dql = "SELECT m FROM $source m";
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

        $dql .= ' ORDER BY m.sentOn DESC';

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        if (null !== $filter->page && null !== $filter->perPage) {
            $doctrineQuery->setFirstResult(($filter->page - 1) * $filter->perPage);
            $doctrineQuery->setMaxResults($filter->perPage);
        } elseif (null !== $filter->perPage) {
            $doctrineQuery->setMaxResults($filter->perPage);
        }

        return $doctrineQuery->toIterable();
    }

    /**
     * Fetch aggregated status counts for an incoming message.
     * Uses a subquery to get the latest status per outgoing message.
     *
     * @return array<string, int>
     */
    private function fetchStatusCounts(Ulid $incomingMessageId): array
    {
        $source = OutgoingMessageEventRecord::class;

        // Get the latest status for each outgoing message
        $dql = <<<DQL
            SELECT rl.status, COUNT(DISTINCT r.outgoingMessageId) as cnt
            FROM $source r
            LEFT JOIN $source rl WITH rl.outgoingMessageId = r.outgoingMessageId
            WHERE r.incomingMessageId = :incomingMessageId
              AND r.status = -1
              AND rl.recordedAt = (
                SELECT MAX(r2.recordedAt)
                FROM $source r2
                WHERE r2.outgoingMessageId = r.outgoingMessageId
              )
            GROUP BY rl.status
            DQL;

        $result = $this->em->createQuery($dql)
            ->setParameter('incomingMessageId', $incomingMessageId, 'ulid')
            ->getResult();

        $statusCounts = [];
        foreach ($result as $row) {
            /** @var OutgoingMessageStatus $status */
            $status = $row['status'];
            $statusCounts[$status->name] = (int) $row['cnt'];
        }

        return $statusCounts;
    }
}
