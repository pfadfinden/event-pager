<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AssignCarrier;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Model\Pager;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class AssignCarrierHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageRecipientRepository $recipientRepository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(AssignCarrier $cmd): void
    {
        $pager = $this->entityManager->getRepository(Pager::class)->find($cmd->pagerId);

        if (!$pager instanceof Pager) {
            throw PagerNotFound::withId($cmd->pagerId);
        }

        $recipient = null;
        if (null !== $cmd->recipientId) {
            $recipient = $this->recipientRepository->getRecipientFromID(Ulid::fromString($cmd->recipientId));
        }

        $pager->setCarriedBy($recipient);

        $this->entityManager->persist($pager);
        $this->uow->commit();
    }
}
