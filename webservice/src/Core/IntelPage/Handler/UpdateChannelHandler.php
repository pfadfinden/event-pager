<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Handler;

use App\Core\Contracts\Bus\Bus;
use App\Core\Contracts\Persistence\UnitOfWork;
use App\Core\IntelPage\Command\AddChannel;
use App\Core\IntelPage\Command\UpdateChannel;
use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\Model\Channel;
use App\View\Web\PagerManagement\Request\ChannelRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: Bus::COMMAND)]
final readonly class UpdateChannelHandler
{
    public function __construct(
        private EntityManagerInterface $repository,
        private UnitOfWork $uow,
    ) {
    }

    public function __invoke(UpdateChannel $cmd): void
    {
        $channel = $this->repository->getRepository(Channel::class)->find($cmd->id);

        $channel->setName($cmd->name);
        $channel->setCapCode(CapCode::fromInt($cmd->capCode));
        $channel->setAudible($cmd->audible);
        $channel->setVibration($cmd->vibration);

        $this->repository->persist($channel);
        $this->uow->commit();
    }
}
