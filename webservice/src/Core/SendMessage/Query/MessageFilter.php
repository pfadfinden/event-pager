<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

readonly class MessageFilter
{
    public function __construct(
        public ?int $offset = null,
        public ?int $limit = null,
    ) {
    }
}
