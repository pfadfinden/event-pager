<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\ExpressionLanguage;

use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Port\ExpressionEvaluationException;
use App\Core\TransportContract\Model\Priority;
use App\Infrastructure\ExpressionLanguage\SymfonyExpressionLanguageEvaluator;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymfonyExpressionLanguageEvaluator::class)]
#[Group('unit')]
final class SymfonyExpressionLanguageEvaluatorTest extends TestCase
{
    private SymfonyExpressionLanguageEvaluator $sut;

    protected function setUp(): void
    {
        $this->sut = new SymfonyExpressionLanguageEvaluator();
    }

    public function testEvaluatesTrueExpression(): void
    {
        $context = $this->createContext();
        $result = $this->sut->evaluate('true', $context);
        self::assertTrue($result);
    }

    public function testEvaluatesFalseExpression(): void
    {
        $context = $this->createContext();
        $result = $this->sut->evaluate('false', $context);
        self::assertFalse($result);
    }

    public function testEvaluatesPriorityValue(): void
    {
        $context = new EvaluationContext(Priority::HIGH, Instant::of(1700000000), 100);
        $result = $this->sut->evaluate('priorityValue >= 40', $context);
        self::assertTrue($result);

        $context = new EvaluationContext(Priority::LOW, Instant::of(1700000000), 100);
        $result = $this->sut->evaluate('priorityValue >= 40', $context);
        self::assertFalse($result);
    }

    public function testEvaluatesPriorityEnum(): void
    {
        $context = new EvaluationContext(Priority::URGENT, Instant::of(1700000000), 100);
        $result = $this->sut->evaluate('priority.value == 50', $context);
        self::assertTrue($result);
    }

    public function testEvaluatesContentLength(): void
    {
        $context = new EvaluationContext(Priority::DEFAULT, Instant::of(1700000000), 150);
        $result = $this->sut->evaluate('contentLength > 100', $context);
        self::assertTrue($result);

        $context = new EvaluationContext(Priority::DEFAULT, Instant::of(1700000000), 50);
        $result = $this->sut->evaluate('contentLength > 100', $context);
        self::assertFalse($result);
    }

    public function testEvaluatesHour(): void
    {
        // 1700000000 = 2023-11-14 22:13:20 UTC
        $context = new EvaluationContext(Priority::DEFAULT, Instant::of(1700000000), 100);
        $result = $this->sut->evaluate('hour >= 22', $context);
        self::assertTrue($result);

        $result = $this->sut->evaluate('hour >= 8 and hour < 18', $context);
        self::assertFalse($result);
    }

    public function testEvaluatesDayOfWeek(): void
    {
        // 1700000000 = 2023-11-14 = Tuesday (dayOfWeek = 2)
        $context = new EvaluationContext(Priority::DEFAULT, Instant::of(1700000000), 100);
        $result = $this->sut->evaluate('dayOfWeek == 2', $context);
        self::assertTrue($result);

        $result = $this->sut->evaluate('dayOfWeek < 6', $context);
        self::assertTrue($result);
    }

    public function testEvaluatesComplexExpression(): void
    {
        // Weekday, high priority, during business hours
        // Using a time that is 10:00 UTC on a Monday
        $monday10am = Instant::of(1699869600); // 2023-11-13 10:00:00 UTC (Monday)
        $context = new EvaluationContext(Priority::HIGH, $monday10am, 100);

        $result = $this->sut->evaluate('dayOfWeek < 6 and priorityValue >= 40 and hour >= 8 and hour < 18', $context);
        self::assertTrue($result);
    }

    public function testThrowsExceptionOnInvalidExpression(): void
    {
        $context = $this->createContext();

        $this->expectException(ExpressionEvaluationException::class);
        $this->sut->evaluate('undefined_variable', $context);
    }

    public function testThrowsExceptionOnSyntaxError(): void
    {
        $context = $this->createContext();

        $this->expectException(ExpressionEvaluationException::class);
        $this->sut->evaluate('((invalid syntax', $context);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function expressionProvider(): iterable
    {
        yield 'literal true' => ['true', true];
        yield 'literal false' => ['false', false];
        yield 'numeric comparison true' => ['1 > 0', true];
        yield 'numeric comparison false' => ['0 > 1', false];
        yield 'and operator' => ['true and true', true];
        yield 'or operator' => ['true or false', true];
        yield 'not operator' => ['not false', true];
        yield 'parentheses' => ['(true and false) or true', true];
    }

    #[DataProvider('expressionProvider')]
    public function testEvaluatesBasicExpressions(string $expression, bool $expected): void
    {
        $context = $this->createContext();
        $result = $this->sut->evaluate($expression, $context);
        self::assertSame($expected, $result);
    }

    private function createContext(): EvaluationContext
    {
        return new EvaluationContext(
            Priority::DEFAULT,
            Instant::of(1700000000),
            100,
        );
    }
}
