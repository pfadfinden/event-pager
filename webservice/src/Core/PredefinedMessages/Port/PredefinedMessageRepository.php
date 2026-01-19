<?php

declare(strict_types=1);

namespace App\Core\PredefinedMessages\Port;

use App\Core\PredefinedMessages\Model\PredefinedMessage;
use Symfony\Component\Uid\Ulid;

interface PredefinedMessageRepository
{
    public function add(PredefinedMessage $predefinedMessage): void;

    public function getById(Ulid $id): ?PredefinedMessage;

    public function remove(PredefinedMessage $predefinedMessage): void;
}
