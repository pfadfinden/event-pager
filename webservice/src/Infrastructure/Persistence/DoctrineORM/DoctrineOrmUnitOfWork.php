<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM;

use App\Core\Contracts\Persistence\UnitOfWork;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOrmUnitOfWork implements UnitOfWork
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function commit(): void
    {
        $this->entityManager->flush();
    }
}
