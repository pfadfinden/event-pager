<?php

declare(strict_types=1);

namespace App\Core\SendMessage\ReadModel;

/**
 * DTO for message history list display - represents an IncomingMessage with status summary.
 */
readonly class MessageHistoryEntry
{
    /**
     * @param array<string, int> $statusCounts Status name => count (e.g., ['QUEUED' => 2, 'TRANSMITTED' => 3])
     * @param string[]           $recipientIds ULIDs of recipients
     */
    public function __construct(
        public string $messageId,
        public string $sentOn,
        public string $sentBy,
        public string $content,
        public int $priority,
        public array $statusCounts,
        public int $totalOutgoing,
        public array $recipientIds,
    ) {
    }
}
