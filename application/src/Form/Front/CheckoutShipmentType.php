<?php

declare(strict_types=1);

namespace App\Form\Front;

use App\Component\Shipment\ShippingCalculator;
use App\Entity\Order;
use App\Entity\ShippingMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/** @extends AbstractType<mixed> */
class CheckoutShipmentType extends AbstractType
{
    public function __construct(private ShippingCalculator $shippingCalculator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Order $cart */
        $cart = $options['cart'];
        $eligibleMethods = $this->shippingCalculator->getEligibleMethods($cart);

        $builder->add('shippingMethod', EntityType::class, [
            'class' => ShippingMethod::class,
            'label' => 'checkout.shipment.choose_method',
            'expanded' => true,
            'multiple' => false,
            'choices' => $eligibleMethods,
            'choice_label' => function (ShippingMethod $method) use ($cart): string {
                $amount = $this->shippingCalculator->computeAmount($method, $cart);
                $price = $amount > 0
                    ? number_format($amount / 100, 2, ',', "\u{202F}") . ' €'
                    : 'Gratuit';

                return sprintf('%s — %s', $method->getName(), $price);
            },
            'constraints' => [new NotNull()],
            'translation_domain' => 'messages',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'translation_domain' => 'messages',
        ]);
        $resolver->setRequired('cart');
        $resolver->setAllowedTypes('cart', Order::class);
    }
}
