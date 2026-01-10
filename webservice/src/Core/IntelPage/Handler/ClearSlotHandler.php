<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\ClearSlot;
use App\Core\IntelPage\Exception\PagerNotFound;
use App\Core\IntelPage\Model\AbstractCapAssignment;
use App\Core\IntelPage\Model\Pager;
use App\Core\IntelPage\Model\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class ClearSlotHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(ClearSlot $cmd): void
    {
        $pager = $this->repository->getRepository(Pager::class)->find($cmd->pagerId);

        if (!$pager instanceof Pager) {
            throw PagerNotFound::withId($cmd->pagerId);
        }

        $capa = $pager->getCapAssignment(Slot::fromInt($cmd->slot));

        if (!$capa instanceof AbstractCapAssignment) {
            return;
        }

        $pager->setLabel($pager->getLabel().'D'.$capa->getSlot()->getSlot());
        $pager->clearSlot(Slot::fromInt($cmd->slot));

        $this->repository->persist($pager);
        $this->uow->commit();
    }
}
