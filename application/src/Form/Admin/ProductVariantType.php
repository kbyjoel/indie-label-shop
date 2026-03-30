<?php

namespace App\Form\Admin;

use App\Entity\Product;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use Aropixel\AdminBundle\Form\Type\FilterableEntitiesType;
use Aropixel\AdminBundle\Form\Type\FilterableEntityType;
use Aropixel\AdminBundle\Form\Type\Select2Type;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantType extends AbstractType
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
            ->add('onHand', IntegerType::class, [
                'label' => 'Stock disponible',
                'required' => true,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'divisor' => 100,
                'required' => false,
            ])
            ->add('tracked', ToggleSwitchType::class, [
                'label' => 'Suivi des stocks',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
        ]);
    }
}
