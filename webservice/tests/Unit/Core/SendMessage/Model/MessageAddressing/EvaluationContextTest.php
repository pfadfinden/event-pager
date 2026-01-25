<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\SendMessage\Model\MessageAddressing;

use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\TransportContract\Model\Priority;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EvaluationContext::class)]
final class EvaluationContextTest extends TestCase
{
    public function testProvidesListOfExpressionVariables(): void
    {
        $sut = new EvaluationContext(
            Priority::HIGH, Instant::of(1768688193), 50
        );
        $vars = $sut->toExpressionVariables();

        self::assertArrayHasKey('priority', $vars);
        self::assertEquals(Priority::HIGH, $vars['priority']);
        self::assertArrayHasKey('hour', $vars);
        self::assertEquals(22, $vars['hour']);
        self::assertArrayHasKey('dayOfWeek', $vars);
        self::assertEquals(6, $vars['dayOfWeek']); // Saturday - timestamp 1768688193 is Jan 17, 2026
        self::assertArrayHasKey('contentLength', $vars);
        self::assertEquals(50, $vars['contentLength']);
    }
}
