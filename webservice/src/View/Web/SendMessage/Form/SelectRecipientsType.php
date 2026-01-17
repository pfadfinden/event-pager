<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration of a ChoiceType to render options of type RecipientListEntry in a SendMessage form.
 *
 * Requires a ChoiceLoader providing a list of RecipientListEntry
 *
 * @extends AbstractType<RecipientListEntry[]>
 */
final class SelectRecipientsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'choice_attr' => ChoiceList::attr($this, function (RecipientListEntry $recipient): array {
                $enabled = true; // @phpstan-ignore-next-line booleanNot.alwaysFalse (TODO validate options)
                if (!$enabled) {
                    return ['class' => 'text-decoration-line-through', 'disabled' => true];
                }

                return [];
            }),
            'choice_value' => ChoiceList::value($this, fn (RecipientListEntry $recipient): string => $recipient->id),
            'choice_label' => ChoiceList::label($this, function (RecipientListEntry $recipient): string {
                $prefix = match ($recipient->type) {
                    'GROUP' => '👥', 'ROLE' => '💼', default => '👤',
                };

                return $prefix.' '.$recipient->name;
            }),
            'group_by' => ChoiceList::groupBy($this, fn (RecipientListEntry $recipient): string => match ($recipient->type) {
                'GROUP' => '👥', 'ROLE' => '💼', default => '👤',
            }),
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
