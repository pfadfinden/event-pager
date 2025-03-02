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
        $recipient = match ($newRecipient->recipientType) {
            'group' => new Group($newRecipient->name),
            'role' => new Role($newRecipient->name, null),
            'person' => new Person($newRecipient->name),
            default => throw new InvalidArgumentException('Invalid recipient type: '.$newRecipient->recipientType),
        };
        $this->repository->add($recipient);
        $this->uow->commit();
    }
}
