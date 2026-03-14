<?php

namespace App\Form\Admin;

use App\Entity\Album;
use App\Entity\AlbumImage;
use App\Entity\AlbumTranslation;
use App\Entity\Band;
use App\Entity\Release;
use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\FilterableEntitiesType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\ModalCollectionType;
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
                'data_class' => AlbumImage::class,
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
//            ->add('artists', Select2Type::class, [
//                'label' => 'Artistes',
//                'repository' => Artist::class,
//                'route' => 'admin_artist_ajax_search',
//                'choice_label' => 'lastName',
//                'multiple' => true,
//                'required' => false,
//            ])
            ->add('similarAlbums', FilterableEntitiesType::class, [
                'label' => 'Albums similaires',
                'repository' => Album::class,
                'route' => 'admin_album_select2',

            ])
            ->add('tracklists', ModalCollectionType::class, [
                'entry_type' => TracklistType::class,
                'columns' => [
                    'Pos.' => 'position',
                    'Titre' => 'track.name',
                    'Master' => 'track.masterFile',
                ],
                'button_add_label' => 'Ajouter un morceau',
                'modal_title' => 'Détails du morceau',
            ])
            ->add('releases', ModalCollectionType::class, [
                'entry_type' => ReleaseType::class,
                'columns' => [
                    'Média' => 'media',
                    'Titre' => 'name',
                    'Prix' => 'price',
                ],
                'button_add_label' => 'Ajouter une release',
                'modal_title' => 'Détails de la release',
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
