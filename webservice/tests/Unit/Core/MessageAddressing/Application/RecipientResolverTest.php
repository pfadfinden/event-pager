<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageAddressing\Application;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\MessageRecipient\Model\Role;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\GroupEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\IndividualEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\RoleEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\RoleEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\RecipientResolver;
use App\Core\SendMessage\Model\MessageAddressing\AddressingResult;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\SendMessage\Port\Clock;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Model\Priority;
use App\Core\TransportContract\Port\Transport;
use Brick\DateTime\Instant;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[CoversClass(RecipientResolver::class)]
#[AllowMockObjectsWithoutExpectations]
final class RecipientResolverTest extends TestCase
{
    private IndividualEvaluator&MockObject $individualEvaluator;
    private RoleEvaluator&MockObject $roleEvaluator;
    private GroupEvaluator&MockObject $groupEvaluator;
    private RecipientResolver $sut;

    protected function setUp(): void
    {
        $this->individualEvaluator = $this->createMock(IndividualEvaluator::class);
        $this->roleEvaluator = $this->createMock(RoleEvaluator::class);
        $this->groupEvaluator = $this->createMock(GroupEvaluator::class);
        $clock = self::createStub(Clock::class);
        $clock->method('now')->willReturn(Instant::of(1700000000));

        $this->sut = new RecipientResolver(
            $this->individualEvaluator,
            $this->roleEvaluator,
            $this->groupEvaluator,
            $clock
        );
    }

    public function testResolvesIndividual(): void
    {
        $person = new Person('Test Person');
        $config = $person->addTransportConfiguration('telegram');
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);
        $message = $this->createMessage();

        $expectedResult = new AddressingResult($person, [$selectedTransport], [], []);

        $this->individualEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($person, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn($expectedResult);

        $results = $this->sut->resolve([$person], $message);

        self::assertCount(1, $results);
        self::assertSame($expectedResult, $results[0]);
    }

    public function testResolvesRoleWithDelegation(): void
    {
        $person = new Person('Assigned Person');
        $role = new Role('On-Call', $person);
        $config = $person->addTransportConfiguration('telegram');
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);
        $message = $this->createMessage();

        $this->roleEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($role, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn(RoleEvaluationResult::delegatedToIndividual($person));

        $expectedResult = new AddressingResult($person, [$selectedTransport], [], []);
        $this->individualEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($person, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn($expectedResult);

        $results = $this->sut->resolve([$role], $message);

        self::assertCount(1, $results);
        self::assertSame($expectedResult, $results[0]);
    }

    public function testResolvesRoleWithoutDelegation(): void
    {
        $role = new Role('Security Officer', null);
        $config = $role->addTransportConfiguration('telegram');
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);
        $message = $this->createMessage();

        $expectedResult = new AddressingResult($role, [$selectedTransport], [], []);

        $this->roleEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($role, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn(RoleEvaluationResult::evaluated($expectedResult));

        $results = $this->sut->resolve([$role], $message);

        self::assertCount(1, $results);
        self::assertSame($expectedResult, $results[0]);
    }

    public function testResolvesGroupWithExpansion(): void
    {
        $group = new Group('Engineering');
        $member = new Person('Member');
        $group->addMember($member);
        $message = $this->createMessage();

        $transport = $this->createMock(Transport::class);
        $groupConfig = $group->addTransportConfiguration('telegram');
        $groupConfig->setContinueInHierarchy(true);
        $groupSelectedTransport = new SelectedTransport($groupConfig, $transport);

        $groupResult = new AddressingResult($group, [$groupSelectedTransport], [], [$member]);

        $memberConfig = $member->addTransportConfiguration('telegram');
        $memberSelectedTransport = new SelectedTransport($memberConfig, $transport);
        $memberResult = new AddressingResult($member, [$memberSelectedTransport], [], []);

        $this->groupEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($group, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn($groupResult);

        $this->individualEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($member, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn($memberResult);

        $results = $this->sut->resolve([$group], $message);

        self::assertCount(2, $results);
        self::assertSame($groupResult, $results[0]);
        self::assertSame($memberResult, $results[1]);
    }

    public function testDeduplicatesRecipients(): void
    {
        $person = new Person('Shared Member');

        $group1 = new Group('Group 1');
        $group1->addMember($person);

        $group2 = new Group('Group 2');
        $group2->addMember($person);

        $transport = $this->createMock(Transport::class);
        $message = $this->createMessage();

        $group1Result = new AddressingResult($group1, [], [], [$person]);
        $group2Result = new AddressingResult($group2, [], [], [$person]);

        $personConfig = $person->addTransportConfiguration('telegram');
        $personSelectedTransport = new SelectedTransport($personConfig, $transport);
        $personResult = new AddressingResult($person, [$personSelectedTransport], [], []);

        $this->groupEvaluator
            ->method('evaluate')
            ->willReturnOnConsecutiveCalls($group1Result, $group2Result);

        $this->individualEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($person, self::isInstanceOf(EvaluationContext::class), $message)
            ->willReturn($personResult);

        $results = $this->sut->resolve([$group1, $group2], $message);

        self::assertCount(3, $results);
    }

    public function testHandlesCyclicGroups(): void
    {
        $groupA = new Group('Cyclic A');
        $groupB = new Group('Cyclic B');
        $groupA->addMember($groupB);
        $groupB->addMember($groupA);
        $message = $this->createMessage();

        $groupAResult = new AddressingResult($groupA, [], [], [$groupB]);
        $groupBResult = new AddressingResult($groupB, [], [], [$groupA]);

        $this->groupEvaluator
            ->method('evaluate')
            ->willReturnOnConsecutiveCalls($groupAResult, $groupBResult);

        $results = $this->sut->resolve([$groupA], $message);

        self::assertCount(2, $results);
    }

    public function testNestedGroupExpansion(): void
    {
        $innerGroup = new Group('Inner');
        $outerGroup = new Group('Outer');
        $person = new Person('Person');
        $message = $this->createMessage();

        $innerGroup->addMember($person);
        $outerGroup->addMember($innerGroup);

        $transport = $this->createMock(Transport::class);

        $outerResult = new AddressingResult($outerGroup, [], [], [$innerGroup]);
        $innerResult = new AddressingResult($innerGroup, [], [], [$person]);

        $personConfig = $person->addTransportConfiguration('telegram');
        $personSelectedTransport = new SelectedTransport($personConfig, $transport);
        $personResult = new AddressingResult($person, [$personSelectedTransport], [], []);

        $this->groupEvaluator
            ->method('evaluate')
            ->willReturnOnConsecutiveCalls($outerResult, $innerResult);

        $this->individualEvaluator
            ->method('evaluate')
            ->willReturn($personResult);

        $results = $this->sut->resolve([$outerGroup], $message);

        self::assertCount(3, $results);
        self::assertSame($outerGroup, $results[0]->recipient);
        self::assertSame($innerGroup, $results[1]->recipient);
        self::assertSame($person, $results[2]->recipient);
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
}
