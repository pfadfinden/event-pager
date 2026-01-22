<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Query\PredefinedMessageById;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class PredefinedMessageByIdQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(PredefinedMessageById $query): ?PredefinedMessageDetail
    {
        if (!Ulid::isValid($query->id)) {
            return null;
        }

        $message = $this->em->getRepository(PredefinedMessage::class)->find(Ulid::fromString($query->id));

        if (null === $message) {
            return null;
        }

        return new PredefinedMessageDetail(
            $message->getId()->toString(),
            $message->getTitle(),
            $message->getMessageContent(),
            $message->getPriority(),
            $message->getRecipientIds(),
            $message->isFavorite(),
            $message->getSortOrder(),
            $message->isEnabled(),
        );
    }
}
