<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\IntelPage\Model\PagerMessage;
use App\Core\IntelPage\Port\PagerMessageRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePagerMessageRepository implements PagerMessageRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function add(PagerMessage $pagerMessage): void
    {
        $this->entityManager->persist($pagerMessage);
    }
}
