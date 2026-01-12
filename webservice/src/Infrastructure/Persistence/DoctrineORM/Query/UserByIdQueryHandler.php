<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\UserById;
use App\Core\UserManagement\ReadModel\UserDetail;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserByIdQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(UserById $query): ?UserDetail
    {
        $user = $this->em->getRepository(User::class)->find($query->id);

        if (null === $user) {
            return null;
        }

        return new UserDetail(
            (int) $user->getId(),
            (string) $user->getUsername(),
            $user->getDisplayname(),
            $user->getRoles(),
        );
    }
}
