<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Query;

readonly class MessageHistoryFilter
{
    public function __construct(
        public ?string $searchText = null,
        public ?string $sentByUserId = null,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {
    }
}
