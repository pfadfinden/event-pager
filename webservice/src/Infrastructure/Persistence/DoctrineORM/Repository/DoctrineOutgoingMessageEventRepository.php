<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\SendMessage\Model\OutgoingMessageEventRecord;
use App\Core\SendMessage\Port\OutgoingMessageEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrineOutgoingMessageEventRepository implements OutgoingMessageEventRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function add(OutgoingMessageEventRecord $record): void
    {
        $this->entityManager->persist($record);
    }

    public function findRecipientIdForOutgoingMessage(Ulid $outgoingMessageId): ?Ulid
    {
        /** @var array{recipientId: Ulid}|null $result */
        $result = $this->entityManager->createQueryBuilder()
            ->select('r.recipientId')
            ->from(OutgoingMessageEventRecord::class, 'r')
            ->where('r.outgoingMessageId = :outgoingMessageId')
            ->setParameter('outgoingMessageId', $outgoingMessageId, 'ulid')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['recipientId'] ?? null;
    }
}
