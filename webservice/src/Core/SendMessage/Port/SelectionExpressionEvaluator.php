<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Port;

use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;

/**
 * Port for evaluating selection expressions used in transport configuration.
 */
interface SelectionExpressionEvaluator
{
    /**
     * Evaluates a selection expression in the given context.
     *
     * @param string            $expression the expression to evaluate
     * @param EvaluationContext $context    the evaluation context with available variables
     *
     * @return bool true if the expression evaluates to true, false otherwise
     *
     * @throws ExpressionEvaluationException if the expression cannot be evaluated
     */
    public function evaluate(string $expression, EvaluationContext $context): bool;
}
