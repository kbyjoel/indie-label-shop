<?php

namespace App\Form\Admin;

use App\Entity\TaxCategory;
use App\Entity\TaxRate;
use App\Entity\Zone;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class TaxRateType extends AbstractType
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
            ->add('amount', PercentType::class, [
                'label' => 'Montant',
                'type' => 'fractional',
                'required' => true,
            ])
            ->add('includedInPrice', ToggleSwitchType::class, [
                'label' => 'Inclus dans le prix',
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => TaxCategory::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('zone', EntityType::class, [
                'label' => 'Zone',
                'class' => Zone::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaxRate::class,
        ]);
    }
}
