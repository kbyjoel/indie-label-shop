<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Product;
use App\Entity\PromotionRule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class PromotionRuleType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de règle',
                'placeholder' => '— Choisir un type —',
                'required' => true,
                'choices' => [
                    'Quantité minimum dans le panier' => 'cart_quantity',
                    'Montant minimum de commande' => 'item_total',
                    'Contient un produit spécifique' => 'contains_product',
                    'Groupe de clients' => 'customer_group',
                    'Nième commande du client' => 'nth_order',
                    'Total articles d\'un taxon' => 'total_of_items_from_taxon',
                ],
            ])
            // cart_quantity / nth_order
            ->add('count', IntegerType::class, [
                'label' => 'Quantité / Numéro de commande',
                'mapped' => false,
                'required' => false,
            ])
            // item_total
            ->add('amount', MoneyType::class, [
                'label' => 'Montant minimum',
                'mapped' => false,
                'required' => false,
                'divisor' => 100,
                'currency' => 'EUR',
            ])
            // contains_product
            ->add('products', EntityType::class, [
                'label' => 'Produits',
                'class' => Product::class,
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
            ])
            // customer_group
            ->add('customerGroupCode', TextType::class, [
                'label' => 'Code du groupe de clients',
                'mapped' => false,
                'required' => false,
            ])
            // total_of_items_from_taxon
            ->add('taxonCode', TextType::class, [
                'label' => 'Code du taxon',
                'mapped' => false,
                'required' => false,
            ])
            ->add('taxonAmount', MoneyType::class, [
                'label' => 'Montant minimum du taxon',
                'mapped' => false,
                'required' => false,
                'divisor' => 100,
                'currency' => 'EUR',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $rule = $event->getData();
            if (!$rule instanceof PromotionRule) {
                return;
            }

            $config = $rule->getConfiguration();
            $form = $event->getForm();

            $form->get('count')->setData($config['count'] ?? $config['nth_order'] ?? null);
            $form->get('amount')->setData($config['WEB']['amount'] ?? null);
            $form->get('customerGroupCode')->setData($config['group_code'] ?? null);
            $form->get('taxonCode')->setData($config['taxon'] ?? null);
            $form->get('taxonAmount')->setData($config['WEB']['amount'] ?? null);

            $productIds = $config['products'] ?? [];
            if ([] !== $productIds) {
                $form->get('products')->setData($this->em->getRepository(Product::class)->findBy(['id' => $productIds]));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PromotionRule::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'promotion_rule';
    }
}
