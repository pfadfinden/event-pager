<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\MessagesSentByUser;
use App\Core\SendMessage\ReadModel\IncomingMessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class MessagesSentByUserQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<IncomingMessageStatus>
     */
    public function __invoke(MessagesSentByUser $query): iterable
    {
        $source = IncomingMessage::class;
        $dto = IncomingMessageStatus::class;
        $dql = <<<EOF
            SELECT NEW $dto(
                m.messageId,
                m.sentOn,
                m.by,
                m.to,
                m.content,
                m.priority,
                'Unknown' )
            FROM $source m
            WHERE m.by = :sentBy
            ORDER BY m.sentOn DESC
            EOF;
        // Doctrine currently uses UUID format to store ULIDs, therefore we have to convert here:
        $parameters = ['sentBy' => Ulid::fromString($query->sentBy)->toRfc4122()];

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        if (null !== $query->filter->limit) {
            $doctrineQuery->setMaxResults($query->filter->limit);
        }
        if (null !== $query->filter->offset) {
            $doctrineQuery->setFirstResult($query->filter->offset);
        }

        return $doctrineQuery->toIterable();
    }
}
