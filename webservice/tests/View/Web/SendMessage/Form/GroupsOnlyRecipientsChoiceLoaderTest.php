<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage\Form;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Model\Group;
use App\Core\MessageRecipient\Query\ListOfMessageRecipients;
use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\View\Web\SendMessage\Form\GroupsOnlyRecipientsChoiceLoader;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[TestGroup('unit')]
final class GroupsOnlyRecipientsChoiceLoaderTest extends TestCase
{
    private QueryBus&MockObject $queryBus;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBus::class);
    }

    public function testLoadChoicesReturnsGroupRecipients(): void
    {
        $expectedRecipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
            new RecipientListEntry('id-2', 'GROUP', 'Team Beta'),
        ];

        $this->queryBus
            ->expects(self::once())
            ->method('get')
            ->with(self::callback(fn (ListOfMessageRecipients $query): bool => Group::class === $query->filterType
                && null === $query->textFilter
                && null === $query->page
                && null === $query->perPage))
            ->willReturn($expectedRecipients);

        $loader = new GroupsOnlyRecipientsChoiceLoader($this->queryBus);
        $choices = $loader->loadChoiceList(fn (RecipientListEntry $recipient): string => $recipient->id)->getChoices();

        self::assertCount(2, $choices);
        self::assertSame($expectedRecipients[0], $choices['id-1']);
        self::assertSame($expectedRecipients[1], $choices['id-2']);
    }

    public function testLoadChoicesWithEmptyResult(): void
    {
        $this->queryBus
            ->expects(self::once())
            ->method('get')
            ->willReturn([]);

        $loader = new GroupsOnlyRecipientsChoiceLoader($this->queryBus);
        $choices = $loader->loadChoiceList(fn (RecipientListEntry $recipient): string => $recipient->id)->getChoices();

        self::assertEmpty($choices);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadValuesForChoicesReturnsRecipientIds(): void
    {
        $recipients = [
            new RecipientListEntry('ulid-abc-123', 'GROUP', 'Team Alpha'),
            new RecipientListEntry('ulid-def-456', 'GROUP', 'Team Beta'),
        ];

        $this->queryBus
            ->method('get')
            ->willReturn($recipients);

        $loader = new GroupsOnlyRecipientsChoiceLoader($this->queryBus);
        $values = $loader->loadValuesForChoices($recipients, fn (RecipientListEntry $recipient): string => $recipient->id);

        self::assertSame(['ulid-abc-123', 'ulid-def-456'], $values);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoadChoicesForValuesReturnsMatchingRecipients(): void
    {
        $recipients = [
            new RecipientListEntry('ulid-abc-123', 'GROUP', 'Team Alpha'),
            new RecipientListEntry('ulid-def-456', 'GROUP', 'Team Beta'),
            new RecipientListEntry('ulid-ghi-789', 'GROUP', 'Team Gamma'),
        ];

        $this->queryBus
            ->method('get')
            ->willReturn($recipients);

        $loader = new GroupsOnlyRecipientsChoiceLoader($this->queryBus);
        $choices = $loader->loadChoicesForValues(['ulid-abc-123', 'ulid-ghi-789'], fn (RecipientListEntry $recipient): string => $recipient->id);

        self::assertCount(2, $choices);
        self::assertSame($recipients[0], $choices[0]);
        self::assertSame($recipients[2], $choices[1]);
    }

    public function testChoiceListIsCached(): void
    {
        $recipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
        ];

        $this->queryBus
            ->expects(self::once())
            ->method('get')
            ->willReturn($recipients);

        $loader = new GroupsOnlyRecipientsChoiceLoader($this->queryBus);

        // Load twice - QueryBus should only be called once
        $loader->loadChoiceList();
        $loader->loadChoiceList();
    }
}
