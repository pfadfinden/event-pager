<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\TransportManager\Model\TransportConfiguration;

/**
 * @implements Query<iterable<TransportConfiguration>>
 */
final readonly class AllTransports implements Query
{
    public static function withoutFilter(): self
    {
        return new self();
    }

    public static function thatAreEnabled(): self
    {
        return new self(true);
    }

    public static function thatAreDisabled(): self
    {
        return new self(false);
    }

    private function __construct(public ?bool $filterEnabledStatus = null)
    {
    }
}
