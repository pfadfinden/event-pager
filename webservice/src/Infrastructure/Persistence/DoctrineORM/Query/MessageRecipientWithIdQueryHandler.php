<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\MessageRecipientWithId;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class MessageRecipientWithIdQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(MessageRecipientWithId $query): RecipientListEntry
    {
        $dql = sprintf("SELECT NEW NAMED %s(r.id, (case
     when r INSTANCE OF %s then 'GROUP'
     when r INSTANCE OF %s then 'ROLE'
     when r INSTANCE OF %s then 'PERSON'
     else 'unknown'
   end) as type, r.name) FROM %s r WHERE r.id = :id", RecipientListEntry::class, Group::class, Role::class, Person::class, AbstractMessageRecipient::class);
        $parameters = ['id' => Ulid::fromString($query->id)->toRfc4122()];

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        // @phpstan-ignore return.type
        return $doctrineQuery->getSingleResult();
    }
}
