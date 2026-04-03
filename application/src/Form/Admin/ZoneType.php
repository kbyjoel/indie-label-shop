<?php

namespace App\Form\Admin;

use App\Entity\Zone;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZoneType extends AbstractType
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
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Pays' => 'country',
                    'Province' => 'province',
                    'Zone' => 'zone',
                ],
                'required' => true,
            ])
            ->add('scope', ChoiceType::class, [
                'label' => 'Portée (Scope)',
                'choices' => [
                    'Toutes' => 'all',
                    'Livraison' => 'shipping',
                    'Taxes' => 'tax',
                ],
                'required' => false,
                'empty_data' => 'all',
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Priorité',
                'required' => true,
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => ZoneMemberType::class,
                'entry_options' => [
                    'zone_type' => $builder->getData()?->getType() ?: 'country',
                ],
                'columns' => [
                    'Code' => 'code',
                ],
                'label' => 'Membres',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}
