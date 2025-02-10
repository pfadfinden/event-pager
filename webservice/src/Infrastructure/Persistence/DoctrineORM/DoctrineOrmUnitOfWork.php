<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM;

use App\Core\Contracts\Persistence\UnitOfWork;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOrmUnitOfWork implements UnitOfWork
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function commit(): void
    {
        $this->entityManager->flush();
    }
}
