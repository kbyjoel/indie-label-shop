<?php

declare(strict_types=1);

namespace App\Twig;

use App\Component\Cart\CartContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    public function __construct(
        private CartContext $cartContext,
        private RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_item_count', $this->cartItemCount(...)),
        ];
    }

    public function cartItemCount(): int
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return 0;
        }

        return $this->cartContext->getCartItemCount($request);
    }
}
