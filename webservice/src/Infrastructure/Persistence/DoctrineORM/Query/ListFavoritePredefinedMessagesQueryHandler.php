<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Query\ListFavoritePredefinedMessages;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ListFavoritePredefinedMessagesQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function __invoke(ListFavoritePredefinedMessages $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(PredefinedMessage::class, 'p')
            ->where('p.isFavorite = :isFavorite')
            ->andWhere('p.isEnabled = :isEnabled')
            ->setParameter('isFavorite', true)
            ->setParameter('isEnabled', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.title', 'ASC')
            ->setMaxResults($query->limit);

        /** @var PredefinedMessage[] $messages */
        $messages = $qb->getQuery()->getResult();

        foreach ($messages as $message) {
            yield new PredefinedMessageListEntry(
                $message->getId()->toString(),
                $message->getTitle(),
                $message->getMessageContent(),
                $message->getPriority(),
                $message->isFavorite(),
                $message->getSortOrder(),
                $message->isEnabled(),
            );
        }
    }
}
