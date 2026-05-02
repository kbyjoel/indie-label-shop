<?php

declare(strict_types=1);

namespace App\Component\Cart;

use App\Entity\Channel;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class CartContext
{
    private const SESSION_KEY = '_cart_token';

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function getCart(Request $request): Order
    {
        $tokenValue = $request->getSession()->get(self::SESSION_KEY);

        if (\is_string($tokenValue) && '' !== $tokenValue) {
            /** @var Order|null $order */
            $order = $this->em->getRepository(Order::class)->findOneBy([
                'tokenValue' => $tokenValue,
                'state' => Order::STATE_CART,
            ]);
            if (null !== $order) {
                return $order;
            }
        }

        return $this->createCart($request);
    }

    public function getCartItemCount(Request $request): int
    {
        $tokenValue = $request->getSession()->get(self::SESSION_KEY);
        if (!\is_string($tokenValue) || '' === $tokenValue) {
            return 0;
        }

        /** @var Order|null $order */
        $order = $this->em->getRepository(Order::class)->findOneBy([
            'tokenValue' => $tokenValue,
            'state' => Order::STATE_CART,
        ]);

        return null !== $order ? $order->getItems()->count() : 0;
    }

    private function createCart(Request $request): Order
    {
        /** @var Channel|null $channel */
        $channel = $this->em->getRepository(Channel::class)->findOneBy([]);
        if (null === $channel) {
            throw new \RuntimeException('No channel found. Create at least one channel.');
        }

        $order = new Order();
        $order->setTokenValue(Uuid::v4()->toRfc4122());
        $order->setChannel($channel);
        $order->setLocaleCode($request->getLocale());
        $order->setCurrencyCode($channel->getBaseCurrency()?->getCode() ?? 'EUR');

        $this->em->persist($order);
        $this->em->flush();

        $request->getSession()->set(self::SESSION_KEY, $order->getTokenValue());

        return $order;
    }
}
