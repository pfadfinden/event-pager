<?php

namespace App\Core\Bus;

interface QueryBus
{
    /**
     * @template T
     *
     * @param Query<T> $query
     *
     * @return T
     */
    public function __invoke(Query $query): mixed;
}
