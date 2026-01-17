<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\DataExchange\Query\ExportChannels;
use App\Core\DataExchange\ReadModel\ChannelExportRow;
use App\Core\IntelPage\Model\Channel;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ExportChannelsQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<ChannelExportRow>
     */
    public function __invoke(ExportChannels $query): iterable
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
            ->from(Channel::class, 'c')
            ->orderBy('c.name', 'ASC');

        foreach ($qb->getQuery()->toIterable() as $channel) {
            /** @var Channel $channel */
            yield new ChannelExportRow(
                $channel->getId()->toRfc4122(),
                $channel->getName() ?? '',
                $channel->getCapCode()->getCode(),
                $channel->isAudible(),
                $channel->isVibration(),
            );
        }
    }
}
