<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;
use Brick\DateTime\Instant;

/**
 * @implements Query<int>
 */
final readonly class CountOutgoingMessageErrors implements Query
{
    public static function since(Instant $since): self
    {
        return new self($since->toDecimal());
    }

    public static function all(): self
    {
        return new self();
    }

    private function __construct(public ?string $since = null)
    {
    }
}
