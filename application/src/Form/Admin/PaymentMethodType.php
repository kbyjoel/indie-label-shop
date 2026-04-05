<?php

namespace App\Form\Admin;

use App\Entity\PaymentMethod;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('enabled', ToggleSwitchType::class, [
                'label' => 'Activé',
                'required' => false,
            ])
            ->add('gatewayType', ChoiceType::class, [
                'label' => 'Gateway',
                'required' => false,
                'placeholder' => '— Aucun —',
                'choices' => [
                    'Stripe' => 'stripe',
                    'PayPal' => 'paypal',
                ],
                'attr' => [
                    'data-payment-gateway-select' => 'true',
                ],
            ])
            // Stripe fields (mapped: false — handled manually in controller)
            ->add('stripePublishableKey', TextType::class, [
                'label' => 'Clé publique (Publishable Key)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('stripeSecretKey', PasswordType::class, [
                'label' => 'Clé secrète (Secret Key)',
                'mapped' => false,
                'required' => false,
                'always_empty' => false,
            ])
            // PayPal fields (mapped: false — handled manually in controller)
            ->add('paypalClientId', TextType::class, [
                'label' => 'Client ID',
                'mapped' => false,
                'required' => false,
            ])
            ->add('paypalSecret', PasswordType::class, [
                'label' => 'Secret',
                'mapped' => false,
                'required' => false,
                'always_empty' => false,
            ])
            ->add('paypalMode', ChoiceType::class, [
                'label' => 'Mode',
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Sandbox (test)' => 'sandbox',
                    'Live (production)' => 'live',
                ],
            ])
        ;

        // Préremplir les champs mapped: false depuis gatewayConfig existant
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $paymentMethod = $event->getData();
            if (!$paymentMethod instanceof PaymentMethod) {
                return;
            }

            $credentials = $paymentMethod->getCredentials() ?? [];
            $form = $event->getForm();

            if ($paymentMethod->getGatewayType() === 'stripe') {
                $form->get('stripePublishableKey')->setData($credentials['publishable_key'] ?? null);
                $form->get('stripeSecretKey')->setData($credentials['secret_key'] ?? null);
            } elseif ($paymentMethod->getGatewayType() === 'paypal') {
                $form->get('paypalClientId')->setData($credentials['client_id'] ?? null);
                $form->get('paypalSecret')->setData($credentials['secret'] ?? null);
                $form->get('paypalMode')->setData($credentials['mode'] ?? 'sandbox');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethod::class,
        ]);
    }
}
