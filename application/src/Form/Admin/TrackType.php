<?php

namespace App\Form\Admin;

use App\Entity\TaxCategory;
use App\Entity\Track;
use App\Entity\TrackMasterFile;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\File\Single\FileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class TrackType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
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
            ->add('masterFile', FileType::class, [
                'label' => 'Fichier Master',
                'data_class' => TrackMasterFile::class,
                'required' => false,
                'accept' => 'audio/flac,audio/wav',
            ])
            ->add('taxCategory', EntityType::class, [
                'class' => TaxCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie de taxe',
                'placeholder' => 'Choisir une catégorie',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $track = $event->getData();
            if (!$track || $track->getId()) {
                return;
            }

            if (!$track->getTaxCategory()) {
                $defaultTaxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForTrack' => true]);
                if ($defaultTaxCategory) {
                    $track->setTaxCategory($defaultTaxCategory);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }
}
