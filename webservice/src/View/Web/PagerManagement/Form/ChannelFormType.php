<?php

declare(strict_types=1);

namespace App\View\Web\PagerManagement\Form;

use App\View\Web\PagerManagement\Request\ChannelRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChannelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('capCode', NumberType::class)
            ->add('audible', CheckboxType::class, [
                'required' => false,
            ])
            ->add('vibration', CheckboxType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->add('reset', ResetType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChannelRequest::class,
        ]);
    }
}
