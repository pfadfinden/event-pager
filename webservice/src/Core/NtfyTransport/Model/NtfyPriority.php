<?php

declare(strict_types=1);

namespace App\Core\NtfyTransport\Model;

use App\Core\TransportContract\Model\Priority;

/**
 * Maps application priority levels to ntfy priority values.
 *
 * @see https://docs.ntfy.sh/publish/#message-priority
 */
enum NtfyPriority: int
{
    case MAX = 5;     // Extended vibration bursts, pop-over notification
    case HIGH = 4;    // Long vibration burst, pop-over notification
    case DEFAULT = 3; // Standard vibration and sound behavior
    case LOW = 2;     // Silent, no visible notification until drawer opens
    case MIN = 1;     // Silent, filed under "Other notifications"

    public static function fromPriority(Priority $priority): self
    {
        return match ($priority) {
            Priority::URGENT => self::MAX,
            Priority::HIGH => self::HIGH,
            Priority::DEFAULT => self::DEFAULT,
            Priority::LOW => self::LOW,
            Priority::MIN => self::MIN,
        };
    }
}
