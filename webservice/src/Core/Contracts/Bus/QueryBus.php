<?php

declare(strict_types=1);

namespace App\Core\Contracts\Bus;

interface QueryBus
{
    /**
     * @template T
     *
     * @param Query<T> $query
     *
     * @return T
     */
    public function get(Query $query): mixed;
}
