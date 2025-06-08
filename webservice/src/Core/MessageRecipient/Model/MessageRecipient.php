<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Model;

interface MessageRecipient extends \App\Core\TransportContract\Model\MessageRecipient
{
    public function getName(): string;
}
