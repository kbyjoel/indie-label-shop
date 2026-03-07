<?php

namespace App\Form\Admin;

use App\Entity\BandImage;
use App\Entity\BandImageCrop;
use App\Entity\BandTranslation;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('slug', HiddenType::class)
            ->add('name', TextType::class, [
                'label'  => 'Nom',
                'required' => true
            ])
            ->add('website', TextType::class, [
                'label'  => 'Site Web',
                'required' => true
            ])
            ->add('email', TextType::class, [
                'label'  => 'Email',
                'required' => true
            ])
            ->add('facebook', TextType::class, [
                'label'  => 'Facebook',
                'required' => true
            ])
            ->add('twitter', TextType::class, [
                'label'  => 'X',
                'required' => true
            ])
            ->add('instagram', TextType::class, [
                'label'  => 'Instagram',
                'required' => true
            ])
            ->add('description', TranslatableType::class, [
                'label'  => 'Description',
                'required' => false,
                'personal_translation' => BandTranslation::class,
                'property_path'        => 'translations',
                'widget' => EditorType::class,
            ])
            ->add('image', ImageType::class, [
                'label'  => 'Image',
                'data_class' => BandImage::class,
                'crop_class' => BandImageCrop::class,
                'required' => true
            ])
        ;
    }

}
