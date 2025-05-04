<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class AddChannel
{
    public function __construct(
        public string $id,
        public string $name,
        public int $capCode,
        public bool $audible,
        public bool $vibration,
    ) {
    }
}
