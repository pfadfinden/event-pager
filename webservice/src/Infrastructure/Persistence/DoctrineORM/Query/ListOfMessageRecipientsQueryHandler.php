<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class ListOfMessageRecipientsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<RecipientListEntry>
     */
    public function __invoke(ListOfMessageRecipients $query): iterable
    {
        $dql = sprintf("SELECT NEW NAMED %s(r.id, (case
     when r INSTANCE OF %s then 'GROUP'
     when r INSTANCE OF %s then 'ROLE'
     when r INSTANCE OF %s then 'PERSON'
     else 'unknown'
   end) as type, r.name) FROM %s r", RecipientListEntry::class, Group::class, Role::class, Person::class, AbstractMessageRecipient::class);
        $parameters = [];

        if (null !== $query->filterType) {
            $dql .= ' WHERE r INSTANCE OF :type';
            $parameters['type'] = $this->em->getClassMetadata($query->filterType);
        }

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters($parameters);

        return $doctrineQuery->getResult();
    }
}
