<?php

namespace App\Form\Admin;

use App\Entity\Track;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée',
                'required' => false,
            ])
            ->add('isrc', TextType::class, [
                'label' => 'ISRC',
                'required' => false,
            ])
            ->add('lyrics', EditorType::class, [
                'label' => 'Paroles',
                'required' => false,
                'toolbar' => 'simple',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }
}
