<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\PromotionAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class PromotionActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'action',
                'placeholder' => '— Choisir un type —',
                'required' => true,
                'choices' => [
                    'Remise % sur la commande' => 'order_percentage_discount',
                    'Remise fixe sur la commande' => 'order_fixed_discount',
                    'Remise % sur chaque article' => 'item_percentage_discount',
                    'Remise fixe par article' => 'item_fixed_discount',
                    'Remise % sur la livraison' => 'shipping_percentage_discount',
                    'Remise fixe par unité' => 'unit_fixed_discount',
                    'Remise % par unité' => 'unit_percentage_discount',
                ],
            ])
            ->add('percentage', PercentType::class, [
                'label' => 'Pourcentage de remise',
                'mapped' => false,
                'required' => false,
                'type' => 'fractional',
                'scale' => 2,
                'help' => 'Entrez 10 pour 10%',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Montant de la remise',
                'mapped' => false,
                'required' => false,
                'divisor' => 100,
                'currency' => 'EUR',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $action = $event->getData();
            if (!$action instanceof PromotionAction) {
                return;
            }

            $config = $action->getConfiguration();
            $form = $event->getForm();

            $form->get('percentage')->setData($config['percentage'] ?? null);
            $form->get('amount')->setData($config['WEB']['amount'] ?? null);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PromotionAction::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'promotion_action';
    }
}
