<?php

declare(strict_types=1);

namespace App\Core\TransportManager\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\TransportManager\Model\TransportConfiguration;

/**
 * @implements Query<TransportConfiguration|null>
 */
final readonly class Transport implements Query
{
    public static function withKey(string $key): self
    {
        return new self($key);
    }

    private function __construct(public string $key)
    {
    }
}
