<?php

namespace App\Form\Admin;

use App\Entity\Country;
use App\Entity\Province;
use App\Entity\Zone;
use App\Entity\ZoneMember;
use Aropixel\AdminBundle\Form\Type\FilterableEntityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ZoneMemberType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $zoneMember = $event->getData();

            $zoneType = $options['zone_type'];
            if ($zoneMember && $zoneMember->getBelongsTo()) {
                $zoneType = $zoneMember->getBelongsTo()->getType();
            }

            $this->addCodeField($form, $zoneType);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            $zoneType = $options['zone_type'];
            // Ici on ne peut pas facilement récupérer le parent car c'est une collection
            // mais on utilise l'option passée par ZoneType qui est à jour si c'est un nouvel objet
            // ou l'objet existant.

            $this->addCodeField($form, $zoneType);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ZoneMember::class,
            'zone_type' => 'country',
        ]);
    }

    /** @param FormInterface<mixed> $form */
    private function addCodeField(FormInterface $form, string $zoneType): void
    {
        $class = match ($zoneType) {
            'country' => Country::class,
            'province' => Province::class,
            'zone' => Zone::class,
            default => Country::class,
        };

        $route = match ($zoneType) {
            'country' => 'admin_country_select2',
            'province' => 'admin_province_select2',
            'zone' => 'admin_zone_select2',
            default => 'admin_country_select2',
        };

        $label = match ($zoneType) {
            'country' => 'Pays',
            'province' => 'Province',
            'zone' => 'Zone',
            default => 'Pays',
        };

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder('code', FilterableEntityType::class, null, [
            'class' => $class,
            'route' => $route,
            'label' => $label,
            'choice_label' => 'name',
            'auto_initialize' => false,
        ]);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($code) use ($class) {
                if (!$code) {
                    return null;
                }

                return $this->entityManager->getRepository($class)->findOneBy(['code' => $code]);
            },
            function ($entity) {
                if (!$entity) {
                    return null;
                }

                return $entity->getCode();
            }
        ));

        $form->add($builder->getForm());
    }
}
