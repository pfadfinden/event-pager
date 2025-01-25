<?php

declare(strict_types=1);

namespace App\Core\Contracts\Bus;

interface CommandBus
{
    public function do(object $command): void;
}
