<?php

declare(strict_types=1);

namespace App\Core\MessageRecipient\Port;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use Symfony\Component\Uid\Ulid;

interface MessageRecipientRepository
{
    public function add(AbstractMessageRecipient $abstractMessageRecipient): void;

    public function getRecipientFromID(Ulid $recipientID): ?AbstractMessageRecipient;

    public function remove(AbstractMessageRecipient $abstractMessageRecipient): void;
}
