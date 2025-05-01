<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

use App\Core\Contracts\Bus\Query;
use Brick\DateTime\Instant;

/**
 * @implements Query<int>
 */
readonly class CountOfSendMessages implements Query
{
    public static function sendByUser(string $sendBy): self
    {
        return new self($sendBy);
    }

    public static function allSendSince(Instant $since): self
    {
        return new self(null, $since->toDecimal());
    }

    public static function all(): self
    {
        return new self();
    }

    private function __construct(public ?string $sendBy = null, public ?string $since = null)
    {
    }
}
