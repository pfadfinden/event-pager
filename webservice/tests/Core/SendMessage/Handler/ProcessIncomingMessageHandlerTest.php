<?php

declare(strict_types=1);

namespace App\Tests\Core\SendMessage\Handler;

use App\Core\Contracts\Bus\EventBus;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Port\MessageRecipientRepository;
use App\Core\SendMessage\Application\MessageAddressing\RecipientResolver;
use App\Core\SendMessage\Command\ProcessIncomingMessage;
use App\Core\SendMessage\Handler\ProcessIncomingMessageHandler;
use App\Core\SendMessage\Model\IncomingMessage;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\SendMessage\Port\IncomingMessageRepository;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\NewOutgoingMessageInitiated;
use App\Core\TransportContract\Port\Transport;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
#[CoversClass(ProcessIncomingMessage::class)]
#[CoversClass(ProcessIncomingMessageHandler::class)]
final class ProcessIncomingMessageHandlerTest extends TestCase
{
    public function testProcessNew(): void
    {
        // Arrange
        $id = Ulid::generate();
        $command = new ProcessIncomingMessage($id);

        $repoM = $this->createMock(IncomingMessageRepository::class);
        $repoM->expects(self::once())->method('getWithId')->willReturn(
            new IncomingMessage(
                Ulid::fromString($id), Instant::now(), Ulid::fromString(Ulid::generate()), [
                    Ulid::fromString(Ulid::generate()),
                    Ulid::fromString(Ulid::generate()),
                ], 'Hello World!', 4
            )
        );

        $person1 = new Person('Person 1');
        $person2 = new Person('Person 2');

        $repoR = $this->createMock(MessageRecipientRepository::class);
        $repoR->expects(self::exactly(2))->method('getRecipientFromID')
            ->willReturnOnConsecutiveCalls($person1, $person2);

        $recipientResolver = $this->createMock(RecipientResolver::class);
        $recipientResolver
            ->expects(self::once())
            ->method('resolve')
            ->with(
                self::logicalAnd(self::isIterable(), self::callback(fn (iterable $list) => iterator_to_array($list) === [$person1, $person2])),
                self::isInstanceOf(Message::class)
            )
            ->willReturn([]);

        $eventBus = $this->createMock(EventBus::class);

        $sut = new ProcessIncomingMessageHandler($repoM, $repoR, $recipientResolver, $eventBus);

        // Act
        $sut($command);

        // Assert = see expectations

        // Cleanup
        DefaultClock::reset();
    }

    public function testSendsMessagesToSelectedTransports(): void
    {
        // Arrange
        $id = Ulid::generate();
        $command = new ProcessIncomingMessage($id);

        $repoM = $this->createMock(IncomingMessageRepository::class);
        $repoM->method('getWithId')->willReturn(
            new IncomingMessage(
                Ulid::fromString($id), Instant::now(), Ulid::fromString(Ulid::generate()), [
                    Ulid::fromString(Ulid::generate()),
                ], 'Test message', 3
            )
        );

        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');

        $repoR = $this->createMock(MessageRecipientRepository::class);
        $repoR->method('getRecipientFromID')->willReturn($person);

        $transport = $this->createMock(Transport::class);
        $transport->expects(self::once())->method('send');

        $selectedTransport = new SelectedTransport($config, $transport);
        $result = new AddressingResult($person, [$selectedTransport], [], []);

        $recipientResolver = $this->createMock(RecipientResolver::class);
        $recipientResolver->method('resolve')->willReturn([$result]);

        $eventBus = $this->createMock(EventBus::class);
        $eventBus->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(NewOutgoingMessageInitiated::class));

        $sut = new ProcessIncomingMessageHandler($repoM, $repoR, $recipientResolver, $eventBus);

        // Act
        $sut($command);
    }
}
