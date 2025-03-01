<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrineMessageRecipientRepository implements MessageRecipientRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function add(AbstractMessageRecipient $abstractMessageRecipient): void
    {
        $this->entityManager->persist($abstractMessageRecipient);
    }

    public function remove(AbstractMessageRecipient $abstractMessageRecipient): void
    {
        $this->entityManager->remove($abstractMessageRecipient);
    }

    public function getRecipientFromID(Ulid $recipientID): ?AbstractMessageRecipient
    {
        return $this->entityManager->getRepository(AbstractMessageRecipient::class)->find($recipientID);
    }
}
