<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use App\View\Web\SendMessage\SendMessageRequest;
use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Standard send message form.
 *
 * @extends AbstractType<SendMessageRequest>
 */
final class SendMessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!isset($options['choice_loader']) || !$options['choice_loader'] instanceof RecipientsChoiceLoader) {
            throw new LogicException('must configure a RecipientsChoiceLoader on SendMessageFormType');
        }

        $builder
            ->add('message', TextareaType::class)
            ->add('priority', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'priority.urgent' => 5,
                    'priority.high' => 4,
                    'priority.normal' => 3,
                    'priority.low' => 2,
                    'priority.minimal' => 1,
                ],
                'choice_translation_domain' => 'messages',
            ])
            // to contains the actually selected list of recipients
            ->add('to', CollectionType::class, [
                'entry_type' => SendMessageRecipientFormType::class,
                'entry_options' => ['required' => false],
                'allow_add' => true,
                'allow_delete' => true,
                // 'keep_as_list' => true, this option results in buggy behaviour when submitting a form with errors
                'delete_empty' => true,
                'prototype' => true,
            ])
            // optionsTo enabled rendering a list of available recipients (groups only)
            ->add('optionsTo', SelectRecipientsType::class, [
                'mapped' => false,
                'choice_loader' => $options['choice_loader'],
                'required' => false,
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
        $resolver->setRequired('choice_loader');
    }
}
