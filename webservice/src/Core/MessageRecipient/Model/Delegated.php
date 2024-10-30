<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

interface Delegated
{
    public function canResolve(): bool;

    /**
     * @return list<MessageRecipient>
     */
    public function resolve(): array;
}
