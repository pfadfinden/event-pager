<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Query\ListPredefinedMessages;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageListEntry;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ListPredefinedMessagesQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<PredefinedMessageListEntry>
     */
    public function __invoke(ListPredefinedMessages $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(PredefinedMessage::class, 'p');

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $qb->where('LOWER(p.title) LIKE LOWER(:textFilter)')
                ->orWhere('LOWER(p.messageContent) LIKE LOWER(:textFilter)')
                ->setParameter('textFilter', '%'.$query->textFilter.'%');
        }

        $qb->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.title', 'ASC');

        if (null !== $query->page && null !== $query->perPage) {
            $qb->setFirstResult(($query->page - 1) * $query->perPage);
            $qb->setMaxResults($query->perPage);
        } elseif (null !== $query->perPage) {
            $qb->setMaxResults($query->perPage);
        }

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
