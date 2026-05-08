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
            ->add('product', EntityType::class, [
                'label' => 'Produit',
                'class' => Product::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'mapped' => false,
                'required' => false,
                'multiple' => false,
            ])
            // customer_group
            ->add('customerGroupCode', TextType::class, [
                'label' => 'Code du groupe de clients',
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $rule = $event->getData();
            if (!$rule instanceof PromotionRule) {
                return;
            }

            $config = $rule->getConfiguration();
            $form = $event->getForm();

            $form->get('count')->setData($config['count'] ?? $config['nth'] ?? null);
            $form->get('amount')->setData($config['WEB']['amount'] ?? null);
            $form->get('customerGroupCode')->setData($config['group_code'] ?? null);

            $productCode = $config['product_code'] ?? null;
            if (null !== $productCode) {
                $form->get('product')->setData($this->em->getRepository(Product::class)->findOneBy(['code' => $productCode]));
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
