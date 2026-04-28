<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\PromotionCoupon;
use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class PromotionCouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('usageLimit', IntegerType::class, [
                'label' => 'Limite d\'utilisation globale',
                'required' => false,
            ])
            ->add('perCustomerUsageLimit', IntegerType::class, [
                'label' => 'Limite par client',
                'required' => false,
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'Date d\'expiration',
                'required' => false,
            ])
            ->add('reusableFromCancelledOrders', CheckboxType::class, [
                'label' => 'Réutilisable après annulation',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PromotionCoupon::class,
        ]);
    }
}
