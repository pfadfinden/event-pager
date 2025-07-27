<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Port\PagerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrinePagerRepository implements PagerRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getById(Ulid $id): ?Pager
    {
        return $this->entityManager->find(Pager::class, $id);
    }

    public function persist(Pager $pager): void
    {
        $this->entityManager->persist($pager);
    }

    public function remove(Pager $pager): void
    {
        $this->entityManager->remove($pager);
    }
}
