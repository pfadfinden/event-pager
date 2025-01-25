<?php

declare(strict_types=1);

namespace App\Core\Contracts\Bus;

interface EventBus
{
    public function publish(object $event): void;
}
