<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Query;

use App\Core\Contracts\Bus\Query;
use App\Core\PredefinedMessages\ReadModel\PredefinedMessageDetail;

/**
 * @implements Query<PredefinedMessageDetail|null>
 */
final readonly class PredefinedMessageById implements Query
{
    public static function withId(string $id): self
    {
        return new self($id);
    }

    private function __construct(public string $id)
    {
    }
}
