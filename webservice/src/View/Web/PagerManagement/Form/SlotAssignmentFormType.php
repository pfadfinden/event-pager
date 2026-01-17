<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Form;

use App\Core\IntelPage\Model\CapCode;
use App\Core\IntelPage\ReadModel\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

/**
 * @extends AbstractType<mixed>
 */
final class SlotAssignmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<Channel> $channels */
        $channels = $options['channels'];

        $builder
            ->add('assignment_type', ChoiceType::class, [
                'choices' => [
                    'Clear' => 0,
                    'Individual Cap Code' => 1,
                    'Channel' => 2,
                ],
            ])
            ->add('channel_id', ChoiceType::class, [
                'choices' => $channels,
                'required' => false,
                'choice_label' => fn (?Channel $channel): ?string => $channel?->name,
                'choice_value' => fn (?Channel $channel): ?string => $channel?->id,
            ])
            ->add('cap_code', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(CapCode::CAP_CODE_MIN),
                    new LessThanOrEqual(CapCode::CAP_CODE_MAX),
                ],
            ])
            ->add('audible', CheckboxType::class, [
                'required' => false,
            ])
            ->add('vibration', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('channels');
        $resolver->setAllowedTypes('channels', 'array');
    }
}
