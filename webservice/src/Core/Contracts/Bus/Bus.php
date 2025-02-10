<?php

declare(strict_types=1);

namespace App\Core\Contracts\Bus;

final class Bus
{
    public const COMMAND = 'command.bus';
    public const EVENT = 'event.bus';
    public const QUERY = 'query.bus';
}
