<?php

declare(strict_types=1);

namespace App\Infrastructure\ExpressionLanguage;

use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Port\ExpressionEvaluationException;
use App\Core\SendMessage\Port\SelectionExpressionEvaluator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

#[AsAlias]
final readonly class SymfonyExpressionLanguageEvaluator implements SelectionExpressionEvaluator
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function evaluate(string $expression, EvaluationContext $context): bool
    {
        try {
            $result = $this->expressionLanguage->evaluate(
                $expression,
                $context->toExpressionVariables(),
            );

            return (bool) $result;
        } catch (Throwable $e) {
            throw ExpressionEvaluationException::fromPrevious($expression, $e);
        }
    }
}
