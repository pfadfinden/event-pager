<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Repository;

use App\Core\IntelPage\Model\Channel;
use App\Core\IntelPage\Port\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DoctrineChannelRepository implements ChannelRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getById(Ulid $id): ?Channel
    {
        return $this->entityManager->find(Channel::class, $id);
    }

    public function persist(Channel $channel): void
    {
        $this->entityManager->persist($channel);
    }

    public function remove(Channel $channel): void
    {
        $this->entityManager->remove($channel);
    }
}
