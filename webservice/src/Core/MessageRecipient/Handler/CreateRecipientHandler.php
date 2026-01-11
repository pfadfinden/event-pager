<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\MessageRecipient\Command\CreateRecipient;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class CreateRecipientHandler
{
    public function __construct(
        private MessageRecipientRepository $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(CreateRecipient $newRecipient): void
    {
        $id = Ulid::fromString($newRecipient->id);
        $recipient = match ($newRecipient->recipientType) {
            'group' => new Group($newRecipient->name, $id),
            'role' => new Role($newRecipient->name, null, $id),
            'person' => new Person($newRecipient->name, $id),
            default => throw new InvalidArgumentException('Invalid recipient type: '.$newRecipient->recipientType),
        };
        $this->repository->add($recipient);
        $this->uow->commit();
    }
}
