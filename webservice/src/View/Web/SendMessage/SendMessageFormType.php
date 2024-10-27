<?php

namespace App\View\Web\SendMessage;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SendMessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message')
            ->add('priority')
            ->add('to', ChoiceType::class, [
                'multiple' => true,
                'choices' => [
                    // TODO dynamic
                    '01J6YVHAW9G41R0C33G6CPRY85',
                    '01J6YVHGARWYBVKAR67JR7T90V',
                    '01J6YVHPG5B9ACNF2JEFRZBCTZ',
                    '01J6YT42VYK4FWMPSCX2W6EQ2W',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Send',
            ])
            ->add('reset', ResetType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendMessageRequest::class,
        ]);
    }
}
