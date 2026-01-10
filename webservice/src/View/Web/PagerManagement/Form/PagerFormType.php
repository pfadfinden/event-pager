<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Form;

use App\View\Web\PagerManagement\Request\PagerRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * @extends AbstractType<PagerRequest>
 */
final class PagerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', NumberType::class, [
                'constraints' => [
                    new NotBlank(message: 'Number is required'),
                    new Positive(message: 'Number must be positive'),
                ],
            ])
            ->add('label', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'Label is required'),
                ],
            ])
            ->add('comment', TextType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PagerRequest::class,
        ]);
    }
}
