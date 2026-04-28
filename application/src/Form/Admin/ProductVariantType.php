<?php

namespace App\Form\Admin;

use App\Entity\ProductVariant;
use App\Entity\TaxCategory;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ProductVariantType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

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
            ->add('taxCategory', EntityType::class, [
                'class' => TaxCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie de taxe',
                'placeholder' => 'Choisir une catégorie',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $variant = $event->getData();
            if (!$variant || $variant->getId()) {
                return;
            }

            if (!$variant->getTaxCategory()) {
                $product = $variant->getProduct();
                $taxCategory = $product?->getTaxCategory();

                if (!$taxCategory) {
                    $taxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForMerch' => true]);
                }

                if ($taxCategory) {
                    $variant->setTaxCategory($taxCategory);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
        ]);
    }
}
