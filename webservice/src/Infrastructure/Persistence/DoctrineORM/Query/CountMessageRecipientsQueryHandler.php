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

        if (null !== $query->filterType) {
            $dql .= ' WHERE r INSTANCE OF :type';
            $parameters['type'] = $this->em->getClassMetadata($query->filterType);
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return (int) $doctrineQuery->getSingleScalarResult();
    }
}
