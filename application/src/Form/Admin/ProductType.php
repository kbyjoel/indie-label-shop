<?php

namespace App\Form\Admin;

use App\Entity\Band;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductTranslation;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Select2Type;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('name', TranslatableType::class, [
                'label' => 'Nom',
                'required' => true,
                'personal_translation' => ProductTranslation::class,
                'property_path' => 'translations',
                'widget' => TextType::class,
            ])
            ->add('description', TranslatableType::class, [
                'label' => 'Description',
                'required' => false,
                'personal_translation' => ProductTranslation::class,
                'property_path' => 'translations',
                'widget' => EditorType::class,
            ])
            ->add('band', EntityType::class, [
                'class' => Band::class,
                'choice_label' => 'name',
                'label' => 'Groupe',
                'placeholder' => 'Choisir un groupe',
                'required' => false,
            ])
            ->add('options', Select2Type::class, [
                'label' => 'Options',
                'repository' => ProductOption::class,
                'route' => 'admin_product_option_ajax_search',
                'choice_label' => 'code',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
