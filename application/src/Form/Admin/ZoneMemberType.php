<?php

namespace App\Form\Admin;

use App\Entity\ZoneMember;
use App\Entity\Country;
use App\Entity\Province;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZoneMemberType extends AbstractType
{
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

    private function addCodeField($form, string $zoneType): void
    {
        if ($zoneType === 'country') {
            $form->add('code', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'Pays',
            ]);
        } elseif ($zoneType === 'province') {
            $form->add('code', EntityType::class, [
                'class' => Province::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'Province',
            ]);
        } elseif ($zoneType === 'zone') {
            $form->add('code', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'Zone',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ZoneMember::class,
            'zone_type' => 'country',
        ]);
    }
}
