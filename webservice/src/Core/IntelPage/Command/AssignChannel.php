<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class AssignChannel
{
    public function __construct(
        public string $pagerId,
        public int $slot,
        public string $channelId,
    ) {
    }
}
