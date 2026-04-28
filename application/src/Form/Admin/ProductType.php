<?php

namespace App\Form\Admin;

use App\Entity\Band;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductTranslation;
use App\Entity\TaxCategory;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\FilterableEntitiesType;
use Aropixel\AdminBundle\Form\Type\SyliusTranslatableType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => false,
                'help' => 'S\'il est laissé vide, le code sera généré automatiquement.',
            ])
            ->add('name', SyliusTranslatableType::class, [
                'label' => 'Nom',
                'required' => true,
                'personal_translation' => ProductTranslation::class,
                'property_path' => 'translations',
                'widget' => TextType::class,
            ])
            ->add('description', SyliusTranslatableType::class, [
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
            ->add('taxCategory', EntityType::class, [
                'class' => TaxCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie de taxe',
                'placeholder' => 'Choisir une catégorie',
                'required' => false,
            ])
            ->add('options', FilterableEntitiesType::class, [
                'label' => 'Variantes',
                'class' => ProductOption::class,
                'route' => 'admin_product_option_select2',
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('variants', CollectionType::class, [
                'entry_type' => ProductVariantType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'list_template' => 'admin/product/variants/collection_list.html.twig',
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
