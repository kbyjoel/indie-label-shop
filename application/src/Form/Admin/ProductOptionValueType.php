<?php

namespace App\Form\Admin;

use App\Entity\ProductOptionValue;
use App\Entity\ProductOptionValueTranslation;
use Aropixel\AdminBundle\Form\Type\SyliusTranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ProductOptionValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => false,
                'help' => 'S\'il est laissé vide, le code sera généré automatiquement.',
            ])
            ->add('value', SyliusTranslatableType::class, [
                'label' => 'Valeur',
                'required' => true,
                'personal_translation' => ProductOptionValueTranslation::class,
                'property_path' => 'translations',
                'widget' => TextType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOptionValue::class,
        ]);
    }
}
