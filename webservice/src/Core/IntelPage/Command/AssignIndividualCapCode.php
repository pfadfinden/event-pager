<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class AssignIndividualCapCode
{
    public function __construct(
        public string $pagerId,
        public int $slot,
        public int $capCode,
        public bool $audible,
        public bool $vibration,
    ) {
    }
}
