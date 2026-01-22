<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model\MessageAddressing;

use App\Core\TransportContract\Model\Priority;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedDateTime;
use DateTimeZone;

/**
 * Context information available during transport configuration expression evaluation.
 */
readonly class EvaluationContext
{
    public ZonedDateTime $currentTime;

    public function __construct(
        public Priority $priority,
        Instant $currentTime,
        public int $contentLength,
    ) {
        $this->currentTime = $currentTime->atTimeZone(
            TimeZone::fromNativeDateTimeZone(new DateTimeZone(date_default_timezone_get()))
        );
    }

    public function getPriorityValue(): int
    {
        return $this->priority->value;
    }

    public function getHour(): int
    {
        return $this->currentTime->getHour();
    }

    public function getDayOfWeek(): int
    {
        return $this->currentTime->getDayOfWeek()->value;
    }

    /**
     * Returns an associative array of all variables available for expression evaluation.
     *
     * @return array<string, mixed>
     */
    public function toExpressionVariables(): array
    {
        return [
            'priority' => $this->priority,
            'priorityValue' => $this->getPriorityValue(),
            'currentTime' => $this->currentTime,
            'hour' => $this->getHour(),
            'dayOfWeek' => $this->getDayOfWeek(),
            'contentLength' => $this->contentLength,
        ];
    }
}
