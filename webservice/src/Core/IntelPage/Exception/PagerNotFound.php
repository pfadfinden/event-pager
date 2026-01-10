<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Exception;

use RuntimeException;
use function sprintf;

final class PagerNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Pager with id "%s" was not found.', $id));
    }
}
