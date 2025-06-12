<?php

declare(strict_types=1);

namespace App\Core\IntelPage\ReadModel;

readonly class Channel
{
    public function __construct(
        public string $id,
        public string $name,
        public int $capCode,
        public bool $audible = true,
        public bool $vibration = true,
    ) {
    }
}
