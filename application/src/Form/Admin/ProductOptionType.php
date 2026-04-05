<?php

namespace App\Form\Admin;

use App\Entity\ProductOption;
use App\Entity\ProductOptionTranslation;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Aropixel\AdminBundle\Form\Type\SyliusTranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => false,
                'help' => 'S\'il est laissé vide, le code sera généré automatiquement.',
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
            ])
            ->add('name', SyliusTranslatableType::class, [
                'label' => 'Nom',
                'required' => true,
                'personal_translation' => ProductOptionTranslation::class,
                'property_path' => 'translations',
                'widget' => TextType::class,
            ])
            ->add('values', CollectionType::class, [
                'entry_type' => ProductOptionValueType::class,
                'columns' => [
                    'Valeur' => [
                        'field' => 'value',
                        'render' => function($field, $item) {
                            return $item->vars['data']?->getValue();
                        },
                    ],
                ],

            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
        ]);
    }
}
