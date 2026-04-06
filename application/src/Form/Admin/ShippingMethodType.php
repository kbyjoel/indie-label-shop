<?php

namespace App\Form\Admin;

use App\Entity\ShippingMethod;
use App\Entity\ShippingMethodTranslation;
use App\Entity\Zone;
use Aropixel\AdminBundle\Form\Type\SyliusTranslatableType;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Aropixel\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class ShippingMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SyliusTranslatableType::class, [
                'label' => 'Nom',
                'required' => true,
                'personal_translation' => ShippingMethodTranslation::class,
                'property_path' => 'translations',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('enabled', ToggleSwitchType::class, [
                'label' => 'Activé',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
            ])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'name',
                'label' => 'Zone',
                'placeholder' => 'Choisir une zone',
                'required' => false,
            ])
            ->add('calculator', ChoiceType::class, [
                'label' => 'Calculateur',
                'required' => false,
                'placeholder' => '— Aucun —',
                'choices' => [
                    'Tarif fixe'        => 'flat_rate',
                    'Par unité'         => 'per_unit_rate',
                    'Tranches de poids' => 'weight_range',
                ],
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Montant (en centimes)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('minDeliveryTimeDays', IntegerType::class, [
                'label' => 'Délai min (jours)',
                'required' => false,
            ])
            ->add('maxDeliveryTimeDays', IntegerType::class, [
                'label' => 'Délai max (jours)',
                'required' => false,
            ])
            ->add('minWeight', IntegerType::class, [
                'label' => 'Poids minimum (grammes)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('maxWeight', IntegerType::class, [
                'label' => 'Poids maximum (grammes)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('brackets', CollectionType::class, [
                'label' => 'Tranches de poids',
                'entry_type' => ShippingMethodBracketType::class,
                'mapped' => false,
                'required' => false,
                'button_add_label' => 'Ajouter une tranche',
                'list_template' => 'admin/shipping_method/_brackets_collection.html.twig',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $method = $event->getData();
            if (!$method instanceof ShippingMethod) {
                return;
            }

            $configuration = $method->getConfiguration();

            if ($method->getCalculator() === 'weight_range') {
                $event->getForm()->get('brackets')->setData($configuration['brackets'] ?? []);
                return;
            }

            $amount = null;
            foreach ($configuration as $channelConfig) {
                if (isset($channelConfig['amount'])) {
                    $amount = $channelConfig['amount'];
                    break;
                }
            }
            $event->getForm()->get('amount')->setData($amount);

            $minWeight = $maxWeight = null;
            foreach ($method->getRules() as $rule) {
                if ($rule->getType() === 'total_weight_greater_than_or_equal') {
                    $minWeight = $rule->getConfiguration()['weight'] ?? null;
                }
                if ($rule->getType() === 'total_weight_less_than_or_equal') {
                    $maxWeight = $rule->getConfiguration()['weight'] ?? null;
                }
            }
            $event->getForm()->get('minWeight')->setData($minWeight);
            $event->getForm()->get('maxWeight')->setData($maxWeight);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethod::class,
        ]);
    }
}
