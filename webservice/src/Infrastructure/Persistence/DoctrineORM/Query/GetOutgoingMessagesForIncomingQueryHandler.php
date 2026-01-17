<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Query\GetOutgoingMessagesForIncoming;
use App\Core\SendMessage\ReadModel\OutgoingMessageDetail;
use App\Core\TransportContract\Model\OutgoingMessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class GetOutgoingMessagesForIncomingQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<OutgoingMessageDetail>
     */
    public function __invoke(GetOutgoingMessagesForIncoming $query): iterable
    {
        $source = OutgoingMessageEventRecord::class;
        $incomingMessageId = Ulid::fromString($query->incomingMessageId)->toRfc4122();

        // Get the latest status for each outgoing message
        $dql = <<<DQL
            SELECT r.outgoingMessageId, r.recipientId, rl.status, rl.recordedAt
            FROM $source r
            LEFT JOIN $source rl ON rl.outgoingMessageId = r.outgoingMessageId AND rl.recordedAt = (
                SELECT MAX(r2.recordedAt)
                FROM $source r2
                WHERE r2.outgoingMessageId = r.outgoingMessageId
            )
            WHERE r.incomingMessageId = :incomingMessageId AND r.status = -1
            ORDER BY r.recordedAt DESC
            DQL;

        $results = $this->em->createQuery($dql)
            ->setParameters(['incomingMessageId' => $incomingMessageId])
            ->getResult();

        // Get recipient names
        $recipientIds = array_filter(array_values(array_unique(array_map(fn (array $r): mixed => $r['recipientId'], $results))), fn ($v): bool => null !== $v);
        $recipientNames = $this->fetchRecipientNames($recipientIds);

        foreach ($results as $row) {
            /** @var Ulid $outgoingMessageId */
            $outgoingMessageId = $row['outgoingMessageId'];
            /** @var Ulid $recipientId */
            $recipientId = $row['recipientId'];
            /** @var OutgoingMessageStatus $status */
            $status = $row['status'];

            yield new OutgoingMessageDetail(
                $outgoingMessageId->toString(),
                $recipientId->toString(),
                $recipientNames[$recipientId->toString()] ?? 'Unknown',
                $status->name,
                $row['recordedAt']->toISOString(),
            );
        }
    }

    /**
     * @param Ulid[] $recipientIds
     *
     * @return array<string, string>
     */
    private function fetchRecipientNames(array $recipientIds): array
    {
        if ([] === $recipientIds) {
            return [];
        }

        $source = AbstractMessageRecipient::class;
        $dql = "SELECT r.id, r.name FROM $source r WHERE r.id IN (:ids)";

        $results = $this->em->createQuery($dql)
            ->setParameter('ids', array_map(fn ($i): string => $i->toRfc4122(), $recipientIds))
            ->getResult();

        $names = [];
        foreach ($results as $row) {
            $names[$row['id']->toString()] = $row['name'];
        }

        return $names;
    }
}
