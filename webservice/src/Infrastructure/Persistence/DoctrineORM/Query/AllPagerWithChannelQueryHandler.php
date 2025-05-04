<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Model\ChannelCapAssignment;
use App\Core\IntelPage\Query\AllPagerWithChannel;
use App\Core\IntelPage\ReadModel\Pager;
use App\Core\IntelPage\ReadModel\PagerInChannel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class AllPagerWithChannelQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<Pager>
     */
    public function __invoke(AllPagerWithChannel $query): iterable
    {
        $dql = sprintf(
            'SELECT NEW %s(p.id, p.label, p.number, ca.slot.slot) FROM %s ca LEFT JOIN ca.pager p LEFT JOIN ca.channel ch WHERE ch.id = :channelId',
            PagerInChannel::class,
            ChannelCapAssignment::class
        );

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters(['channelId' => Ulid::fromString($query->channelId)->toRfc4122()]);

        return $doctrineQuery->toIterable();
    }
}
