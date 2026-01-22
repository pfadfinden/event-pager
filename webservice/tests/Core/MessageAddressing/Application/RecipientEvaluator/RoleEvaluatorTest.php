<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\RoleEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\RoleEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingError;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\Transport;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RoleEvaluator::class)]
#[CoversClass(RoleEvaluationResult::class)]
#[Group('unit')]
final class RoleEvaluatorTest extends TestCase
{
    public function testDelegatesToIndividualWhenAssigned(): void
    {
        $person = new Person('Assigned Person');
        $role = new Role('On-Call Engineer', $person);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator->expects($this->never())->method('evaluate');

        $sut = new RoleEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($role, $context, $message);

        self::assertTrue($result->isDelegatedToIndividual);
        self::assertSame($person, $result->delegatedIndividual);
        self::assertNull($result->addressingResult);
    }

    public function testEvaluatesRoleConfigurationsWhenNoIndividualAssigned(): void
    {
        $role = new Role('Security Officer', null);
        $config = $role->addTransportConfiguration('telegram');

        /** @var Transport&\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);
        $message = $this->createMessage();

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($role, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn(new TransportConfigurationEvaluationResult(
                [$selectedTransport],
                [],
            ));

        $sut = new RoleEvaluator($configEvaluator);
        $context = $this->createContext();

        $result = $sut->evaluate($role, $context, $message);

        self::assertFalse($result->isDelegatedToIndividual);
        self::assertNull($result->delegatedIndividual);
        self::assertNotNull($result->addressingResult);
        self::assertTrue($result->addressingResult->hasSelectedTransports());
        self::assertSame($role, $result->addressingResult->recipient);
    }

    public function testReturnsErrorWhenNoIndividualAndNoConfigurations(): void
    {
        $role = new Role('Intern Role', null);
        $error = AddressingError::noTransportConfigurations($role);
        $message = $this->createMessage();

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn(new TransportConfigurationEvaluationResult(
                [],
                [$error],
            ));

        $sut = new RoleEvaluator($configEvaluator);
        $context = $this->createContext();

        $result = $sut->evaluate($role, $context, $message);

        self::assertFalse($result->isDelegatedToIndividual);
        self::assertNotNull($result->addressingResult);
        self::assertTrue($result->addressingResult->hasErrors());
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
