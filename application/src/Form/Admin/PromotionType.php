<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Promotion;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('exclusive', ToggleSwitchType::class, [
                'label' => 'Exclusive (ne se cumule pas)',
                'required' => false,
            ])
            ->add('couponBased', ToggleSwitchType::class, [
                'label' => 'Requiert un coupon',
                'required' => false,
            ])
            ->add('appliesToDiscounted', ToggleSwitchType::class, [
                'label' => 'S\'applique aux articles déjà remisés',
                'required' => false,
            ])
            ->add('usageLimit', IntegerType::class, [
                'label' => 'Limite d\'utilisation globale',
                'required' => false,
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Date de début',
                'required' => false,
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Date de fin',
                'required' => false,
            ])
            ->add('rules', CollectionType::class, [
                'entry_type' => PromotionRuleType::class,
                'by_reference' => false,
                'columns' => [
                    'Type' => ['field' => 'type', 'display' => 'label'],
                ],
                'button_add_label' => 'Ajouter une règle',
                'form_title' => 'Détails de la règle',
            ])
            ->add('actions', CollectionType::class, [
                'entry_type' => PromotionActionType::class,
                'by_reference' => false,
                'columns' => [
                    'Type' => ['field' => 'type', 'display' => 'label'],
                ],
                'button_add_label' => 'Ajouter une action',
                'form_title' => 'Détails de l\'action',
            ])
            ->add('coupons', CollectionType::class, [
                'entry_type' => PromotionCouponType::class,
                'by_reference' => false,
                'columns' => [
                    'Code' => ['field' => 'code', 'display' => 'label'],
                ],
                'button_add_label' => 'Ajouter un coupon',
                'form_title' => 'Détails du coupon',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
}
