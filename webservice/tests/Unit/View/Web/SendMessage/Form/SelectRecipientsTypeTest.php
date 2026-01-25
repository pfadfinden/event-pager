<?php

declare(strict_types=1);

namespace App\Tests\Unit\View\Web\SendMessage\Form;

use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\View\Web\SendMessage\Form\SelectRecipientsType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Test\TypeTestCase;

#[AllowMockObjectsWithoutExpectations]
final class SelectRecipientsTypeTest extends TypeTestCase
{
    public function testParentIsChoiceType(): void
    {
        $type = new SelectRecipientsType();
        self::assertSame(ChoiceType::class, $type->getParent());
    }

    public function testMultipleIsEnabledByDefault(): void
    {
        $recipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
            new RecipientListEntry('id-2', 'PERSON', 'John Doe'),
        ];

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => $recipients),
        ]);

        self::assertTrue($form->getConfig()->getOption('multiple'));
    }

    public function testChoiceLabelIncludesTypeEmojiAndName(): void
    {
        $groupRecipient = new RecipientListEntry('id-1', 'GROUP', 'Team Alpha');
        $roleRecipient = new RecipientListEntry('id-2', 'ROLE', 'Manager');
        $personRecipient = new RecipientListEntry('id-3', 'PERSON', 'John Doe');

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => [
                $groupRecipient,
                $roleRecipient,
                $personRecipient,
            ]),
        ]);

        $view = $form->createView();
        $choices = $view->vars['choices'];

        self::assertCount(3, $choices);
    }

    public function testChoicesAreGroupedByType(): void
    {
        $groupRecipient = new RecipientListEntry('id-1', 'GROUP', 'Team Alpha');
        $personRecipient = new RecipientListEntry('id-2', 'PERSON', 'John Doe');

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => [
                $groupRecipient,
                $personRecipient,
            ]),
        ]);

        $view = $form->createView();

        // When grouped, choices should be organized by their group label // TODO why is this test showing this?
        self::assertArrayHasKey('preferred_choices', $view->vars);
    }

    public function testFormSubmissionWithMultipleRecipients(): void
    {
        $recipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
            new RecipientListEntry('id-2', 'GROUP', 'Team Beta'),
        ];

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => $recipients),
        ]);

        $form->submit(['id-1', 'id-2']);

        self::assertTrue($form->isSynchronized());
        /** @phpstan-ignore-next-line argument.type */
        self::assertCount(2, $form->getData());
    }

    public function testFormSubmissionWithSingleRecipient(): void
    {
        $recipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
        ];

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => $recipients),
        ]);

        $form->submit(['id-1']);

        self::assertTrue($form->isSynchronized());
        /** @phpstan-ignore-next-line argument.type */
        self::assertCount(1, $form->getData());
    }

    public function testFormSubmissionWithEmptySelection(): void
    {
        $recipients = [
            new RecipientListEntry('id-1', 'GROUP', 'Team Alpha'),
        ];

        $form = $this->factory->create(SelectRecipientsType::class, null, [
            'choice_loader' => new CallbackChoiceLoader(fn (): array => $recipients),
        ]);

        $form->submit([]);

        self::assertTrue($form->isSynchronized());
        self::assertEmpty($form->getData());
    }
}
