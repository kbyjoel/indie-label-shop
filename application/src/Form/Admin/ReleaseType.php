<?php

namespace App\Form\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\Release;
use App\Entity\Tracklist;
use Aropixel\AdminBundle\Form\Type\Select2Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'divisor' => 1,
                'required' => true,
            ])
            ->add('media', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'name',
                'label' => 'Média',
                'placeholder' => 'Choisir un média',
                'required' => true,
            ])
            ->add('album', EntityType::class, [
                'class' => Album::class,
                'choice_label' => 'title',
                'label' => 'Album',
                'placeholder' => 'Choisir un album',
                'required' => true,
                'property_path' => 'product',
            ])
            ->add('tracklists', Select2Type::class, [
                'label' => 'Tracklists',
                'repository' => Tracklist::class,
                'route' => 'admin_tracklist_ajax_search',
                'choice_label' => function (Tracklist $tracklist) {
                    return sprintf('#%d - %s', $tracklist->getPosition(), $tracklist->getTrack()?->getTitle() ?: 'Unknown');
                },
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Release::class,
        ]);
    }
}
