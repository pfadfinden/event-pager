<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use App\Core\SendMessage\Model\OutgoingMessageEventRecord;

interface OutgoingMessageEventRepository
{
    public function add(OutgoingMessageEventRecord $record): void;
}
