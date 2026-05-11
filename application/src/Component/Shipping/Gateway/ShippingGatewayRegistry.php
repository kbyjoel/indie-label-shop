<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ShippingGatewayRegistry
{
    /** @param iterable<ShippingGatewayInterface> $gateways */
    public function __construct(
        #[AutowireIterator('app.shipping_gateway')]
        private readonly iterable $gateways,
    ) {
    }

    public function get(string $code): ShippingGatewayInterface
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->getCode() === $code) {
                return $gateway;
            }
        }

        throw new \InvalidArgumentException(sprintf('No shipping gateway registered with code "%s".', $code));
    }

    /** @return ShippingGatewayInterface[] */
    public function all(): array
    {
        return iterator_to_array($this->gateways, false);
    }
}
