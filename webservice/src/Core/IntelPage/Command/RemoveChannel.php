<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class RemoveChannel
{
    public function __construct(
        public string $id,
    ) {
    }
}
