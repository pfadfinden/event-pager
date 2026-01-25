<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageAddressing\Application;

use App\Core\MessageRecipient\Model\Person;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingErrorType;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Port\ExpressionEvaluationException;
use App\Core\SendMessage\Port\SelectionExpressionEvaluator;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\Transport;
use App\Core\TransportContract\Port\TransportManager;
use Brick\DateTime\Instant;
use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(TransportConfigurationEvaluator::class)]
#[CoversClass(TransportConfigurationEvaluationResult::class)]
#[AllowMockObjectsWithoutExpectations]
final class TransportConfigurationEvaluatorTest extends TestCase
{
    private SelectionExpressionEvaluator&MockObject $expressionEvaluator;
    private TransportManager&Stub $transportManager;
    private TransportConfigurationEvaluator $sut;

    protected function setUp(): void
    {
        $this->expressionEvaluator = $this->createMock(SelectionExpressionEvaluator::class);
        $this->transportManager = self::createStub(TransportManager::class);
        $this->sut = new TransportConfigurationEvaluator(
            $this->expressionEvaluator,
            $this->transportManager,
        );
    }

    public function testEmptyConfigurationsReturnsError(): void
    {
        $person = new Person('Test Person');
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertCount(1, $result->errors);
        self::assertSame(AddressingErrorType::NO_TRANSPORT_CONFIGURATIONS, $result->errors[0]->type);
    }

    public function testSelectsMatchingConfiguration(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');
        $config->setSelectionExpression('true');
        $message = $this->createMessage();

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->with('telegram')
            ->willReturn($transport);

        $this->expressionEvaluator
            ->method('evaluate')
            ->with('true', self::isInstanceOf(EvaluationContext::class))
            ->willReturn(true);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertFalse($result->hasErrors());
        self::assertCount(1, $result->selectedTransports);
        self::assertSame($config, $result->selectedTransports[0]->configuration);
        self::assertSame($transport, $result->selectedTransports[0]->transport);
    }

    public function testSkipsDisabledConfiguration(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');
        $config->isEnabled = false;
        $message = $this->createMessage();

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertSame(AddressingErrorType::NO_MATCHING_CONFIGURATIONS, $result->errors[0]->type);
    }

    public function testSkipsTransportThatDoesNotAcceptMessages(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');
        $config->setSelectionExpression('true');
        $message = $this->createMessage();

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(false);

        $this->transportManager
            ->method('transportWithKey')
            ->with('telegram')
            ->willReturn($transport);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertSame(AddressingErrorType::NO_MATCHING_CONFIGURATIONS, $result->errors[0]->type);
    }

    public function testReturnsErrorWhenTransportNotFound(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('unknown-transport');
        $message = $this->createMessage();

        $this->transportManager
            ->method('transportWithKey')
            ->with('unknown-transport')
            ->willReturn(null);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertSame(AddressingErrorType::TRANSPORT_NOT_FOUND, $result->errors[0]->type);
    }

    public function testEvaluatesConfigurationsInRankOrder(): void
    {
        $person = new Person('Test Person');
        $message = $this->createMessage();

        $config1 = $person->addTransportConfiguration('low-rank');
        $config1->setRank(10);
        $config1->setSelectionExpression('true');

        $config2 = $person->addTransportConfiguration('high-rank');
        $config2->setRank(100);
        $config2->setSelectionExpression('true');
        $config2->setEvaluateOtherTransportConfigurations(false);

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->willReturn($transport);

        $evaluatedExpressions = [];
        $this->expressionEvaluator
            ->method('evaluate')
            ->willReturnCallback(function (string $expression) use (&$evaluatedExpressions): bool {
                $evaluatedExpressions[] = $expression;

                return true;
            });

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertCount(1, $result->selectedTransports);
        self::assertSame('high-rank', $result->selectedTransports[0]->configuration->getKey());
    }

    public function testStopsEvaluatingWhenEvaluateOtherIsFalse(): void
    {
        $person = new Person('Test Person');
        $message = $this->createMessage();

        $config1 = $person->addTransportConfiguration('first');
        $config1->setRank(100);
        $config1->setSelectionExpression('true');
        $config1->setEvaluateOtherTransportConfigurations(false);

        $config2 = $person->addTransportConfiguration('second');
        $config2->setRank(50);
        $config2->setSelectionExpression('true');

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->willReturn($transport);

        $this->expressionEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->willReturn(true);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertCount(1, $result->selectedTransports);
        self::assertSame('first', $result->selectedTransports[0]->configuration->getKey());
    }

    public function testContinuesEvaluatingWhenExpressionFails(): void
    {
        $person = new Person('Test Person');
        $message = $this->createMessage();

        $config1 = $person->addTransportConfiguration('failing');
        $config1->setRank(100);
        $config1->setSelectionExpression('invalid');

        $config2 = $person->addTransportConfiguration('working');
        $config2->setRank(50);
        $config2->setSelectionExpression('true');

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->willReturn($transport);

        $this->expressionEvaluator
            ->method('evaluate')
            ->willReturnCallback(function (string $expression): bool {
                if ('invalid' === $expression) {
                    throw ExpressionEvaluationException::fromPrevious($expression, new Exception('Syntax error'));
                }

                return true;
            });

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertCount(1, $result->selectedTransports);
        self::assertSame('working', $result->selectedTransports[0]->configuration->getKey());
        self::assertCount(1, $result->errors);
        self::assertSame(AddressingErrorType::EXPRESSION_EVALUATION_FAILED, $result->errors[0]->type);
    }

    public function testShouldStopHierarchyExpansionWithFalseFlag(): void
    {
        $person = new Person('Test Person');
        $message = $this->createMessage();

        $config = $person->addTransportConfiguration('telegram');
        $config->setSelectionExpression('true');
        $config->setContinueInHierarchy(false);

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->willReturn($transport);

        $this->expressionEvaluator
            ->method('evaluate')
            ->willReturn(true);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertTrue($result->shouldStopHierarchyExpansion());
    }

    public function testShouldNotStopHierarchyExpansionWithTrueOrNullFlag(): void
    {
        $person = new Person('Test Person');
        $message = $this->createMessage();

        $config1 = $person->addTransportConfiguration('telegram');
        $config1->setRank(100);
        $config1->setSelectionExpression('true');
        $config1->setContinueInHierarchy(true);

        $config2 = $person->addTransportConfiguration('email');
        $config2->setRank(50);
        $config2->setSelectionExpression('true');
        $config2->setContinueInHierarchy(null);

        $transport = $this->createMock(Transport::class);
        $transport->method('acceptsNewMessages')->willReturn(true);
        $transport->method('canSendTo')->willReturn(true);

        $this->transportManager
            ->method('transportWithKey')
            ->willReturn($transport);

        $this->expressionEvaluator
            ->method('evaluate')
            ->willReturn(true);

        $context = $this->createContext();

        $result = $this->sut->evaluate($person, $context, $message);

        self::assertFalse($result->shouldStopHierarchyExpansion());
    }

    private function createMessage(): Message
    {
        return new class implements Message {
            public Ulid $messageId {
                get {
                    return Ulid::fromString('01JT62N5PE9HBQTEZ1PPE6CJ4F');
                }
            }
            public string $body = 'Test message';
            public Priority $priority = Priority::DEFAULT;
        };
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
