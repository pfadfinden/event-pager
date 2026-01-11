<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class AssignCarrier
{
    public function __construct(
        public string $pagerId,
        public ?string $recipientId = null,
    ) {
    }
}
