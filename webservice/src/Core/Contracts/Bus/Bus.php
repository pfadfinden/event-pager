<?php

declare(strict_types=1);

namespace App\Core\Contracts\Bus;

final class Bus
{
    public const string COMMAND = 'command.bus';
    public const string EVENT = 'event.bus';
    public const string QUERY = 'query.bus';
}
