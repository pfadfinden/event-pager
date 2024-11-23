<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use Doctrine\ORM\EntityManagerInterface;

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
}
