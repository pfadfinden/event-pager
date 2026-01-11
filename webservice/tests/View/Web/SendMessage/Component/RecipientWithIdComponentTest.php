<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage\Component;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Query\MessageRecipientWithId;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\View\Web\SendMessage\Component\RecipientWithIdComponent;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class RecipientWithIdComponentTest extends TestCase
{
    private QueryBus&MockObject $queryBus;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBus::class);
    }

    public function testGetRecipientQueriesWithProvidedId(): void
    {
        $expectedRecipient = new RecipientListEntry('test-ulid-123', 'GROUP', 'Team Alpha');

        $this->queryBus
            ->expects(self::once())
            ->method('get')
            ->with(self::callback(function (MessageRecipientWithId $query): bool {
                return 'test-ulid-123' === $query->id;
            }))
            ->willReturn($expectedRecipient);

        $component = new RecipientWithIdComponent($this->queryBus);
        $component->id = 'test-ulid-123';

        $result = $component->getRecipient();

        self::assertSame($expectedRecipient, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRecipientReturnsGroupRecipient(): void
    {
        $expectedRecipient = new RecipientListEntry('group-id', 'GROUP', 'Development Team');

        $this->queryBus
            ->method('get')
            ->willReturn($expectedRecipient);

        $component = new RecipientWithIdComponent($this->queryBus);
        $component->id = 'group-id';

        $result = $component->getRecipient();

        self::assertSame('group-id', $result->id);
        self::assertSame('GROUP', $result->type);
        self::assertSame('Development Team', $result->name);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRecipientReturnsRoleRecipient(): void
    {
        $expectedRecipient = new RecipientListEntry('role-id', 'ROLE', 'Manager');

        $this->queryBus
            ->method('get')
            ->willReturn($expectedRecipient);

        $component = new RecipientWithIdComponent($this->queryBus);
        $component->id = 'role-id';

        $result = $component->getRecipient();

        self::assertSame('role-id', $result->id);
        self::assertSame('ROLE', $result->type);
        self::assertSame('Manager', $result->name);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetRecipientReturnsPersonRecipient(): void
    {
        $expectedRecipient = new RecipientListEntry('person-id', 'PERSON', 'John Doe');

        $this->queryBus
            ->method('get')
            ->willReturn($expectedRecipient);

        $component = new RecipientWithIdComponent($this->queryBus);
        $component->id = 'person-id';

        $result = $component->getRecipient();

        self::assertSame('person-id', $result->id);
        self::assertSame('PERSON', $result->type);
        self::assertSame('John Doe', $result->name);
    }

    public function testMultipleCallsQueryEachTime(): void
    {
        $recipient1 = new RecipientListEntry('id-1', 'GROUP', 'Team 1');
        $recipient2 = new RecipientListEntry('id-2', 'GROUP', 'Team 2');

        $this->queryBus
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($recipient1, $recipient2);

        $component = new RecipientWithIdComponent($this->queryBus);

        $component->id = 'id-1';
        $result1 = $component->getRecipient();

        $component->id = 'id-2';
        $result2 = $component->getRecipient();

        self::assertSame($recipient1, $result1);
        self::assertSame($recipient2, $result2);
    }
}
