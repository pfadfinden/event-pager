<?php

declare(strict_types=1);

namespace App\Core\IntelPage\ReadModel;

final class PagerInChannel
{
    public function __construct(
        public string $id,
        public string $label,
        public int $number,
        public int $slot,
        // TODO isActive
        // TODO carriedBy
    ) {
    }
}
