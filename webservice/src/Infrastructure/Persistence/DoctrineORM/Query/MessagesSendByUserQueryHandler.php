<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Query\MessagesSendByUser;
use App\Core\SendMessage\ReadModel\SendMessageStatus;
use Doctrine\ORM\EntityManagerInterface;

final class MessagesSendByUserQueryHandler
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<SendMessageStatus>
     */
    public function __invoke(MessagesSendByUser $query): iterable
    {
        $dql = 'SELECT NEW '.SendMessageStatus::class.'(m.messageId, m.sendOn, m.sendBy, m.content, m.priority, \'Unknown\') FROM '.IncomingMessage::class.' m WHERE m.sendBy = :sendBy';
        $parameters = ['sendBy' => $query->sendBy];

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
