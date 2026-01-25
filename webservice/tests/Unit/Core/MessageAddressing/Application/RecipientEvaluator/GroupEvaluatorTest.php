<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\MessageAddressing\Application\RecipientEvaluator;

use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Model\Person;
use App\Core\SendMessage\Application\MessageAddressing\RecipientEvaluator\GroupEvaluator;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluationResult;
use App\Core\SendMessage\Application\MessageAddressing\TransportConfigurationEvaluator;
use App\Core\SendMessage\Model\MessageAddressing\AddressingErrorType;
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

#[CoversClass(GroupEvaluator::class)]
#[AllowMockObjectsWithoutExpectations]
final class GroupEvaluatorTest extends TestCase
{
    public function testEmptyGroupWithNoConfigurationsReturnsError(): void
    {
        $group = new Group('Empty Group');

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasErrors());
        self::assertSame(AddressingErrorType::EMPTY_GROUP_NO_CONFIGURATIONS, $result->errors[0]->type);
        self::assertSame([], $result->membersToExpand);
    }

    public function testGroupWithNoConfigurationsExpandsMembers(): void
    {
        $group = new Group('No Config Group');
        $member1 = new Person('Member 1');
        $member2 = new Person('Member 2');
        $group->addMember($member1);
        $group->addMember($member2);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertFalse($result->hasErrors());
        self::assertTrue($result->hasMembersToExpand());
        self::assertCount(2, $result->membersToExpand);
        self::assertContains($member1, $result->membersToExpand);
        self::assertContains($member2, $result->membersToExpand);
    }

    public function testGroupWithNoMatchingConfigurationsExpandsMembers(): void
    {
        $group = new Group('Group');
        $config = $group->addTransportConfiguration('telegram');
        $config->setSelectionExpression('false');

        $member = new Person('Member');
        $group->addMember($member);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn(new TransportConfigurationEvaluationResult([], []));

        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertFalse($result->hasSelectedTransports());
        self::assertTrue($result->hasMembersToExpand());
        self::assertContains($member, $result->membersToExpand);
    }

    public function testGroupWithMatchingConfigAndContinueInHierarchyTrueExpandsMembers(): void
    {
        $group = new Group('Group');
        $config = $group->addTransportConfiguration('telegram');
        $config->setContinueInHierarchy(true);

        $member = new Person('Member');
        $group->addMember($member);

        /** @var Transport&\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn(new TransportConfigurationEvaluationResult([$selectedTransport], []));

        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertTrue($result->hasMembersToExpand());
        self::assertContains($member, $result->membersToExpand);
    }

    public function testGroupWithMatchingConfigAndContinueInHierarchyFalseDoesNotExpand(): void
    {
        $group = new Group('Group');
        $config = $group->addTransportConfiguration('telegram');
        $config->setContinueInHierarchy(false);

        $member = new Person('Member');
        $group->addMember($member);

        /** @var Transport&\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);

        $evalResult = new TransportConfigurationEvaluationResult([$selectedTransport], []);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn($evalResult);

        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertFalse($result->hasMembersToExpand());
    }

    public function testGroupWithMixedContinueInHierarchyDoesNotExpand(): void
    {
        $group = new Group('Mixed Group');
        $config1 = $group->addTransportConfiguration('telegram');
        $config1->setRank(100);
        $config1->setContinueInHierarchy(true);

        $config2 = $group->addTransportConfiguration('email');
        $config2->setRank(50);
        $config2->setContinueInHierarchy(false);

        $member = new Person('Member');
        $group->addMember($member);

        /** @var Transport&\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);
        $selectedTransport1 = new SelectedTransport($config1, $transport);
        $selectedTransport2 = new SelectedTransport($config2, $transport);

        $evalResult = new TransportConfigurationEvaluationResult(
            [$selectedTransport1, $selectedTransport2],
            [],
        );

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn($evalResult);

        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertFalse($result->hasMembersToExpand());
    }

    public function testGroupWithNoMembersAndMatchingConfigDoesNotExpand(): void
    {
        $group = new Group('No Member Group');
        $config = $group->addTransportConfiguration('telegram');
        $config->setContinueInHierarchy(true);

        /** @var Transport&\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);
        $selectedTransport = new SelectedTransport($config, $transport);

        /** @var TransportConfigurationEvaluator&\PHPUnit\Framework\MockObject\MockObject $configEvaluator */
        $configEvaluator = $this->createMock(TransportConfigurationEvaluator::class);
        $configEvaluator
            ->method('evaluate')
            ->willReturn(new TransportConfigurationEvaluationResult([$selectedTransport], []));

        $sut = new GroupEvaluator($configEvaluator);
        $context = $this->createContext();
        $message = $this->createMessage();

        $result = $sut->evaluate($group, $context, $message);

        self::assertTrue($result->hasSelectedTransports());
        self::assertFalse($result->hasMembersToExpand());
        self::assertFalse($result->hasErrors());
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
