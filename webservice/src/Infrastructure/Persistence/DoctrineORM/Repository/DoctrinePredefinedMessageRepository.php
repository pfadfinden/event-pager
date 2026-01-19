<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use App\Core\PredefinedMessages\Port\PredefinedMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrinePredefinedMessageRepository implements PredefinedMessageRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function add(PredefinedMessage $predefinedMessage): void
    {
        $this->entityManager->persist($predefinedMessage);
    }

    public function getById(Ulid $id): ?PredefinedMessage
    {
        return $this->entityManager->getRepository(PredefinedMessage::class)->find($id);
    }

    public function remove(PredefinedMessage $predefinedMessage): void
    {
        $this->entityManager->remove($predefinedMessage);
    }
}
