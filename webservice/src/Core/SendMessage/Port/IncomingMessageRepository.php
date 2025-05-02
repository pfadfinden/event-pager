<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use App\Core\SendMessage\Model\IncomingMessage;
use Symfony\Component\Uid\Ulid;

interface IncomingMessageRepository
{
    public function getWithId(Ulid $id): ?IncomingMessage;

    public function add(IncomingMessage $incomingMessage): void;
}
