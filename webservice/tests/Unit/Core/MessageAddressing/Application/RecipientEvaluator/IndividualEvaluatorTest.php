<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageAddressing\Application\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Person;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\IndividualEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingError;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\Transport;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(IndividualEvaluator::class)]
#[AllowMockObjectsWithoutExpectations]
final class IndividualEvaluatorTest extends TestCase
{
    public function testEvaluatesPersonWithConfigurations(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');

        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);
        $message = $this->createMessage();

        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($person, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn(new TransportConfigurationEvaluationResult(
                [$selectedTransport],
                [],
            ));

        $sut = new IndividualEvaluator($configEvaluator);
        $context = $this->createContext();

        $result = $sut->evaluate($person, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertFalse($result->hasErrors());
        self::assertSame($person, $result->recipient);
        self::assertCount(1, $result->selectedTransports);
        self::assertSame($selectedTransport, $result->selectedTransports[0]);
        self::assertSame([], $result->membersToExpand);
    }

    public function testReturnsErrorsFromConfigurationEvaluator(): void
    {
        $person = new Person('Test Person');
        $error = AddressingError::noTransportConfigurations($person);
        $message = $this->createMessage();

        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn(new TransportConfigurationEvaluationResult(
                [],
                [$error],
            ));

        $sut = new IndividualEvaluator($configEvaluator);
        $context = $this->createContext();

        $result = $sut->evaluate($person, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertSame($error, $result->errors[0]);
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
