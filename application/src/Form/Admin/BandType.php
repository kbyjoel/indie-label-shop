<?php

namespace App\Form\Admin;

use App\Entity\BandImage;
use App\Entity\BandImageCrop;
use App\Entity\BandTranslation;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/** @extends AbstractType<mixed> */
class BandType extends AbstractType
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';

        $builder
            ->add('status', HiddenType::class)
            ->add('slug', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('website', TextType::class, [
                'label' => 'Site Web',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook',
                'required' => false,
            ])
            ->add('twitter', TextType::class, [
                'label' => 'X',
                'required' => false,
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram',
                'required' => false,
            ])
            ->add('description', TranslatableType::class, [
                'label' => 'Description',
                'required' => false,
                'personal_translation' => BandTranslation::class,
                'property_path' => 'translations',
                'widget' => EditorType::class,
            ])
            ->add('image', ImageType::class, [
                'label' => 'Image',
                'data_class' => BandImage::class,
                'crop_class' => BandImageCrop::class,
                'required' => false,
            ])
            ->add('concerts', CollectionType::class, [
                'entry_type' => ConcertType::class,
                'by_reference' => false,
                'columns' => [
                    'Date' => [
                        'field' => 'date',
                        'render' => function (\Symfony\Component\Form\FormView $field, \Symfony\Component\Form\FormView $item) use ($locale): string {
                            $date = $item->vars['data']?->getDate();
                            if (!$date instanceof \DateTimeInterface) {
                                return '';
                            }
                            $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);

                            return $formatter->format($date) ?: '';
                        },
                    ],
                    'Ville'  => ['field' => 'city',   'display' => 'label'],
                    'Lieu'   => ['field' => 'venue',  'display' => 'label'],
                    'Statut' => ['field' => 'status', 'display' => 'label'],
                ],
                'button_add_label' => 'Ajouter un concert',
                'form_title' => 'Détails du concert',
            ])
            ->add('videoClips', CollectionType::class, [
                'entry_type' => BandVideoClipType::class,
                'by_reference' => false,
                'list_template' => 'admin/band/video_clips/_collection_list.html.twig',
                'entry_row_template' => 'admin/band/video_clips/_collection_row.html.twig',
                'button_add_label' => 'Ajouter un clip vidéo',
                'form_title' => 'Code du clip vidéo',
                'sortable' => true,
            ])
        ;
    }
}
