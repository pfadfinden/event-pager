<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Exception;

use RuntimeException;
use function sprintf;

final class IntelPageMessageTooLong extends RuntimeException
{
    public static function withLength(int $length): self
    {
        return new self(sprintf('The message was too long with %d bytes, the maximum allowed is 512.', $length));
    }
}
