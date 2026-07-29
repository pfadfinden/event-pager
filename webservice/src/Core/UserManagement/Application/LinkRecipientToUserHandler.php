<?php

declare(strict_types=1);

namespace App\Core\UserManagement\Application;

use App\Core\Contracts\Bus\Bus;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\UserManagement\Command\LinkRecipientToUser;
use App\Core\UserManagement\Model\User;
use App\Infrastructure\Persistence\DoctrineORM\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class LinkRecipientToUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(LinkRecipientToUser $command): void
    {
        $user = $this->userRepository->findOneByUsername($command->getUsername());

        if (!$user instanceof User) {
            throw new InvalidArgumentException('User not found');
        }

        $recipientId = $command->getRecipientId();
        if (null === $recipientId) {
            $user->setRecipient(null);
            $this->userRepository->save($user);

            return;
        }

        $recipient = $this->em->find(AbstractMessageRecipient::class, Ulid::fromString($recipientId));
        if (!$recipient instanceof AbstractMessageRecipient) {
            throw new InvalidArgumentException('Recipient not found');
        }

        $user->setRecipient($recipient);
        $this->userRepository->save($user);
    }
}
