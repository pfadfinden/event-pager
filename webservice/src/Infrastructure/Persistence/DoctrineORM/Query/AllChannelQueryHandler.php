<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Query\AllChannel;
use App\Core\IntelPage\ReadModel\Channel;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;

final readonly class AllChannelQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<Channel>
     */
    public function __invoke(AllChannel $query): iterable
    {
        $dql = sprintf(
            'SELECT NEW %s(ch.id, ch.name, ch.capCode.code, ch.audible, ch.vibration) FROM %s ch ORDER BY ch.capCode.code ASC',
            Channel::class,
            \App\Core\IntelPage\Model\Channel::class
        );

        $doctrineQuery = $this->em->createQuery($dql);

        return $doctrineQuery->toIterable();
    }
}
