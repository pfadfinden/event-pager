<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Exception;

use RuntimeException;
use function sprintf;

final class ChannelNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Channel with id "%s" was not found.', $id));
    }
}
