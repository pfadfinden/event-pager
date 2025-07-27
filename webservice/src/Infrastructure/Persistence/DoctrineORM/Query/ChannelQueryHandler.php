<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\DoctrineORM\Query;

use App\Core\IntelPage\Query\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Ulid;
use function sprintf;

final readonly class ChannelQueryHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(Channel $query): \App\Core\IntelPage\ReadModel\Channel
    {
        $dql = sprintf(
            'SELECT NEW %s(ch.id, ch.name, ch.capCode.code, ch.audible, ch.vibration) FROM %s ch WHERE ch.id = :channelId',
            \App\Core\IntelPage\ReadModel\Channel::class,
            \App\Core\IntelPage\Model\Channel::class
        );

        $doctrineQuery = $this->em->createQuery($dql);
        $doctrineQuery->setParameters(['channelId' => Ulid::fromString($query->id)->toRfc4122()]);

        // @phpstan-ignore return.type
        return $doctrineQuery->getSingleResult();
    }
}
