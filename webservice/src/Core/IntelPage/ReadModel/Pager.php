<?php

declare(strict_types=1);

namespace App\Core\IntelPage\ReadModel;

final class Pager
{
    public function __construct(
        public string $id,
        public string $label,
        public int $number,
        public ?string $comment = null,
        public bool $isActive = false,
        public ?string $carriedById = null,
        public ?string $carriedByName = null,
        // TODO channel
    ) {
    }
}
