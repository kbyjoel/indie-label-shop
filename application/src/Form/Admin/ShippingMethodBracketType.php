<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ShippingMethodBracketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('min', IntegerType::class, [
                'label' => 'Poids min (g)',
                'required' => true,
                'attr' => ['min' => 0],
            ])
            ->add('max', IntegerType::class, [
                'label' => 'Poids max (g)',
                'required' => false,
                'attr' => ['min' => 0, 'placeholder' => 'Illimité'],
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Tarif (centimes)',
                'required' => true,
                'attr' => ['min' => 0],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
