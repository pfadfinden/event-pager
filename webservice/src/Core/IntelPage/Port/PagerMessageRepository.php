<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use App\Core\IntelPage\Model\PagerMessage;

interface PagerMessageRepository
{
    public function add(PagerMessage $pagerMessage): void;
}
