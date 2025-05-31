<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Command;

use App\Core\IntelPage\Model\CapCode;
use Symfony\Component\Uid\Ulid;

readonly class QueueMessage
{
    public static function with(
        Ulid $id,
        CapCode $capCode,
        string $message,
        int $priority,
        Ulid $incomingMessageId,
    ): self {
        return new self($id, $capCode, $message, $priority, $incomingMessageId);
    }

    public function __construct(
        public Ulid $id,
        public CapCode $capCode,
        public string $message,
        public int $priority,
        public Ulid $incomingMessageId,
    ) {
    }
}
