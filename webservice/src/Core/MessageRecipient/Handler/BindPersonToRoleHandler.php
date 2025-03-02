<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\BindPersonToRole;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class BindPersonToRoleHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    /**
     * Invoke with value null for the person to remove a person from a role.
     */
    public function __invoke(BindPersonToRole $bindPersonToRole): void
    {
        $role = $this->repository->getRecipientFromID($bindPersonToRole->getRoleID());
        if (!$role instanceof Role) {
            throw new InvalidArgumentException("Role with ID {$bindPersonToRole->getRoleID()} not found.");
        }

        $person = null === $bindPersonToRole->getPersonID() ? null : $this->getPerson($bindPersonToRole->getPersonID());

        $role->bindPerson($person);
        $this->uow->commit();
    }

    public function getPerson(Ulid $personId): Person
    {
        $person = $this->repository->getRecipientFromID($personId);
        if (!$person instanceof Person) {
            throw new InvalidArgumentException("Person with ID {$personId} not found.");
        }

        return $person;
    }
}
