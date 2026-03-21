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
                'required' => true,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
            ])
            ->add('onHand', IntegerType::class, [
                'label' => 'Stock disponible',
                'required' => true,
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix (en centimes)',
                'required' => false,
            ])
            ->add('tracked', ToggleSwitchType::class, [
                'label' => 'Suivi des stocks',
                'required' => false,
            ])
            ->add('product', FilterableEntityType::class, [
                'repository' => Product::class,
                'route' => 'admin_product_select2',
                'choice_label' => 'name',
                'label' => 'Produit',
                'placeholder' => 'Choisir un produit',
                'required' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $variant = $event->getData();
            $form = $event->getForm();

            if (!$variant || !$variant->getProduct()) {
                return;
            }

            $product = $variant->getProduct();
            foreach ($product->getOptions() as $option) {
                $optionValue = null;
                foreach ($variant->getOptionValues() as $value) {
                    if ($value->getOption() === $option) {
                        $optionValue = $value;
                        break;
                    }
                }

                $form->add('option_' . $option->getId(), Select2Type::class, [
                    'label' => $option->getName(),
                    'repository' => ProductOptionValue::class,
                    'route' => 'admin_product_option_value_select2',
                    'route_params' => ['optionId' => $option->getId()],
                    'choice_label' => 'name',
                    'required' => false,
                    'mapped' => false,
                    'data' => $optionValue,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $variant = $event->getData();
            $form = $event->getForm();

            if (!$variant || !$variant->getProduct()) {
                return;
            }

            $product = $variant->getProduct();
            $newOptionValues = [];

            foreach ($product->getOptions() as $option) {
                $fieldName = 'option_' . $option->getId();
                if ($form->has($fieldName)) {
                    $optionValue = $form->get($fieldName)->getData();
                    if ($optionValue) {
                        $newOptionValues[] = $optionValue;
                    }
                }
            }

            // Mettre à jour la collection optionValues
            // On peut soit vider et rajouter, soit être plus fin.
            // Comme c'est une relation ManyToMany, on peut utiliser clear() et add().
            $variant->getOptionValues()->clear();
            foreach ($newOptionValues as $value) {
                $variant->addOptionValue($value);
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
