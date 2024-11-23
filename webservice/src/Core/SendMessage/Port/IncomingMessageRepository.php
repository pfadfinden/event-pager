<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use App\Core\SendMessage\Model\IncomingMessage;

interface IncomingMessageRepository
{
    public function add(IncomingMessage $incomingMessage): void;
}
