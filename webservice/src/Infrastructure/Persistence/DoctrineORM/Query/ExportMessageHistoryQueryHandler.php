<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\DataExchange\Query\ExportMessageHistory;
use App\Core\DataExchange\ReadModel\MessageHistoryExportRow;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function array_map;
use function implode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class ExportMessageHistoryQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<MessageHistoryExportRow>
     */
    public function __invoke(ExportMessageHistory $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m')
            ->from(IncomingMessage::class, 'm')
            ->orderBy('m.sentOn', 'DESC');

        if ($query->from instanceof DateTimeImmutable) {
            $qb->andWhere('m.sentOn >= :from')
                ->setParameter('from', $query->from->format('Y-m-d H:i:s'));
        }

        if ($query->to instanceof DateTimeImmutable) {
            $qb->andWhere('m.sentOn <= :to')
                ->setParameter('to', $query->to->format('Y-m-d H:i:s'));
        }

        foreach ($qb->getQuery()->toIterable() as $message) {
            /** @var IncomingMessage $message */
            yield new MessageHistoryExportRow(
                $message->messageId->toRfc4122(),
                $message->sentOn->toISOString(),
                $message->by->toRfc4122(),
                implode(',', array_map(fn (Ulid $ulid) => $ulid->toRfc4122(), $message->to)),
                $message->content,
                $message->priority,
                $this->getStatusSummary($message->messageId),
            );
        }
    }

    private function getStatusSummary(Ulid $messageId): string
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('o.status, COUNT(o.id) as cnt')
            ->from(OutgoingMessageEventRecord::class, 'o')
            ->where('o.incomingMessageId = :messageId')
            ->setParameter('messageId', $messageId)
            ->groupBy('o.status');

        $results = $qb->getQuery()->getResult();
        /** @var array<string, int> $summary */
        $summary = [];

        foreach ($results as $row) {
            $status = $row['status'];
            $key = $status instanceof OutgoingMessageStatus ? $status->value : (string) $status;
            $summary[$key] = (int) $row['cnt'];
        }

        return json_encode($summary, JSON_THROW_ON_ERROR);
    }
}
