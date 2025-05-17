<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrineIncomingMessageRepository implements IncomingMessageRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function add(IncomingMessage $incomingMessage): void
    {
        $this->entityManager->persist($incomingMessage);
    }

    public function getWithId(Ulid $id): ?IncomingMessage
    {
        return $this->entityManager->getRepository(IncomingMessage::class)->find($id);
    }
}
