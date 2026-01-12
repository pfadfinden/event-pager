<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\UserManagement\Model\User;
use App\Core\UserManagement\Query\ListUsers;
use App\Core\UserManagement\ReadModel\UserListEntry;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ListUsersQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<UserListEntry>
     */
    public function __invoke(ListUsers $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u');

        if (null !== $query->textFilter && '' !== $query->textFilter) {
            $qb->where('LOWER(u.username) LIKE LOWER(:textFilter)')
                ->orWhere('LOWER(u.displayname) LIKE LOWER(:textFilter)')
                ->setParameter('textFilter', '%'.$query->textFilter.'%');
        }

        $qb->orderBy('u.username', 'ASC');

        if (null !== $query->page && null !== $query->perPage) {
            $qb->setFirstResult(($query->page - 1) * $query->perPage);
            $qb->setMaxResults($query->perPage);
        } elseif (null !== $query->perPage) {
            $qb->setMaxResults($query->perPage);
        }

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();

        foreach ($users as $user) {
            yield new UserListEntry(
                (int) $user->getId(),
                (string) $user->getUsername(),
                $user->getDisplayname(),
                $user->getRoles(),
            );
        }
    }
}
