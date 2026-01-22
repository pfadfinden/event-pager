<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use Exception;
use Throwable;
use function sprintf;

/**
 * Exception thrown when expression evaluation fails.
 */
class ExpressionEvaluationException extends Exception
{
    public static function fromPrevious(string $expression, Throwable $previous): self
    {
        return new self(
            sprintf('Failed to evaluate expression "%s": %s', $expression, $previous->getMessage()),
            0,
            $previous,
        );
    }
}
