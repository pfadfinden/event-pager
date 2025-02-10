<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus\SymfonyMessenger;

use App\Core\Contracts\Bus\Query;
use App\Core\Contracts\Bus\QueryBus;
use LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class SymfonyQueryBus implements QueryBus
{
    public function __construct(private MessageBusInterface $queryBus)
    {
    }

    /**
     * @template T
     *
     * @param Query<T> $query
     *
     * @return T
     */
    public function get(Query $query): mixed
    {
        $handledStamp = $this->queryBus->dispatch($query)->last(HandledStamp::class);
        if (null === $handledStamp) {
            throw new LogicException('Query was not handled. Ensure all query handles are tagged and loaded.');
        }

        return $handledStamp->getResult();
    }
}
