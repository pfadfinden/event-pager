<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class RemovePager
{
    public function __construct(
        public string $id,
    ) {
    }
}
