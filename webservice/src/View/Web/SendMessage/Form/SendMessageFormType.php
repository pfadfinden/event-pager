<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use App\Core\MessageRecipient\ReadModel\RecipientListEntry;
use App\View\Web\SendMessage\SendMessageRecipientRequest;
use App\View\Web\SendMessage\SendMessageRequest;
use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                    'Urgent' => 5,
                    'High' => 4,
                    'Normal' => 3,
                    'Low' => 2,
                    'Minimal' => 1,
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
            ->addEventListener(FormEvents::SUBMIT, $this->onSubmit(...))
        ;
    }

    /**
     * No-JS fallback: populate 'to' from 'optionsTo' selections before validation.
     *
     * When JavaScript is disabled, users select recipients directly from the
     * optionsTo multi-select. This event listener converts those selections
     * to the expected SendMessageRecipientRequest format before validation.
     */
    private function onSubmit(FormEvent $event): void
    {
        /** @var SendMessageRequest $data */
        $data = $event->getData();

        // Only populate if 'to' is empty (no-JS fallback)
        if ([] !== $data->to) {
            return;
        }

        /** @var RecipientListEntry[] $selectedRecipients */
        $selectedRecipients = $event->getForm()->get('optionsTo')->getData() ?? [];

        foreach ($selectedRecipients as $recipient) {
            $recipientRequest = new SendMessageRecipientRequest();
            $recipientRequest->id = $recipient->id;
            $recipientRequest->label = $recipient->name;
            $recipientRequest->type = $recipient->type;
            $data->to[] = $recipientRequest;
        }

        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendMessageRequest::class,
            // Use session-based CSRF instead of stateless (double-submit cookie)
            // to ensure no-JS compatibility
            'csrf_token_id' => 'send_message',
        ]);
        $resolver->setRequired('choice_loader');
    }
}
