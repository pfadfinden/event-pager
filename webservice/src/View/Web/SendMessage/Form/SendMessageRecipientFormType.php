<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use App\View\Web\SendMessage\SendMessageRecipientRequest;
use App\View\Web\SendMessage\SendMessageRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Subform to enable form submissions to include recipient metadata without additional database queries.
 *
 * @extends AbstractType<SendMessageRequest>
 */
final class SendMessageRecipientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, ['attr' => ['data-property' => 'id']])
            ->add('label', HiddenType::class, ['attr' => ['data-property' => 'label']])
            ->add('type', HiddenType::class, ['attr' => ['data-property' => 'type']])
            ->add('enabledTransports', HiddenType::class, ['attr' => ['data-property' => 'enabledTransports']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendMessageRecipientRequest::class,
        ]);
    }
}
