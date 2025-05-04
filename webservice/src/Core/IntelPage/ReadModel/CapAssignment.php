<?php

declare(strict_types=1);

namespace App\Core\IntelPage\ReadModel;

final class CapAssignment
{
    public function __construct(
        public string $slot,
        public string $type,
        public int $capCode,
        public bool $audible,
        public bool $vibration,
        public ?string $channel = null,
    ) {
    }
}
