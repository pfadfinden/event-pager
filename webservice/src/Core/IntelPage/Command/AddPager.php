<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

final readonly class AddPager
{
    public function __construct(
        public string $id,
        public string $label,
        public int $number,
    ) {
    }
}
