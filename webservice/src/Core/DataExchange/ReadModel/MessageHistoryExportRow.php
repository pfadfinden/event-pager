<?php

declare(strict_types=1);

namespace App\Core\DataExchange\ReadModel;

final readonly class MessageHistoryExportRow implements ExportRowInterface
{
    public function __construct(
        public string $messageId,
        public string $sentOn,
        public string $sentById,
        public string $recipientIds,
        public string $content,
        public int $priority,
        public string $statusSummary,
    ) {
    }

    /**
     * @return string[]
     */
    public static function csvHeaders(): array
    {
        return ['message_id', 'sent_on', 'sent_by_id', 'recipient_ids', 'content', 'priority', 'status_summary'];
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'message_id' => $this->messageId,
            'sent_on' => $this->sentOn,
            'sent_by_id' => $this->sentById,
            'recipient_ids' => $this->recipientIds,
            'content' => $this->content,
            'priority' => $this->priority,
            'status_summary' => $this->statusSummary,
        ];
    }
}
