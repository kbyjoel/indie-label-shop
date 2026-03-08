<?php

namespace App\Form\Admin;

use App\Entity\Album;
use App\Entity\AlbumTranslation;
use App\Entity\Artist;
use App\Entity\Band;
use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\Select2Type;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('slug', HiddenType::class)
            ->add('artwork', ImageType::class, [
                'label' => 'Image',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('catalogNumber', TextType::class, [
                'label' => 'Numéro de catalogue',
                'required' => false,
            ])
            ->add('releaseDate', DateTimeType::class, [
                'label' => 'Date de sortie',
                'required' => false,
            ])
            ->add('description', TranslatableType::class, [
                'label' => 'Description',
                'required' => false,
                'personal_translation' => AlbumTranslation::class,
                'property_path' => 'albumTranslations',
                'widget' => EditorType::class,
            ])
            ->add('band', EntityType::class, [
                'class' => Band::class,
                'choice_label' => 'name',
                'label' => 'Groupe',
                'placeholder' => 'Choisir un groupe',
                'required' => false,
            ])
            ->add('artists', Select2Type::class, [
                'label' => 'Artistes',
                'repository' => Artist::class,
                'route' => 'admin_artist_ajax_search',
                'choice_label' => 'lastName',
                'multiple' => true,
                'required' => false,
            ])
            ->add('similarAlbums', Select2Type::class, [
                'label' => 'Albums similaires',
                'repository' => Album::class,
                'route' => 'admin_album_ajax_search',
                'choice_label' => 'title',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }
}
