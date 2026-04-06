<?php

namespace App\Form\Admin;

use App\Entity\Media;
use App\Entity\TaxCategory;
use App\Entity\Release;
use App\Entity\ReleaseImage;
use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ReleaseType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Hors ligne' => Publishable::STATUS_OFFLINE,
                    'En ligne' => Publishable::STATUS_ONLINE,
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'divisor' => 100,
                'required' => true,
            ])
            ->add('media', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'name',
                'label' => 'Média',
                'placeholder' => 'Choisir un média',
                'required' => true,
            ])
            ->add('image', ImageType::class, [
                'label' => 'Image',
                'data_class' => ReleaseImage::class,
                'required' => false,
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
            $release = $event->getData();
            if (!$release || $release->getId()) {
                return;
            }

            if (!$release->getTaxCategory()) {
                $album = $release->getAlbum();
                $taxCategory = $album?->getTaxCategory();

                if (!$taxCategory) {
                    $taxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForAlbum' => true]);
                }

                if ($taxCategory) {
                    $release->setTaxCategory($taxCategory);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Release::class,
        ]);
    }
}
