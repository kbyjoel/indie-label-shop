<?php

declare(strict_types=1);

namespace App\Component\Cart;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ProductVariant;
use Doctrine\ORM\EntityManagerInterface;

class CartManager
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function addItem(Order $cart, ProductVariant $variant, int $qty = 1): void
    {
        foreach ($cart->getItems() as $existing) {
            if (!$existing instanceof OrderItem) {
                continue;
            }
            if ($existing->getVariant() === $variant) {
                $existing->setQuantity($existing->getQuantity() + $qty);
                $this->recalculateItem($existing);
                $this->recalculate($cart);
                $this->em->flush();

                return;
            }
        }

        $item = new OrderItem();
        $item->setVariant($variant);
        $item->setQuantity($qty);
        $item->setUnitPrice($variant->getPrice() ?? 0);
        $cart->addItem($item);
        $this->recalculateItem($item);
        $this->recalculate($cart);
        $this->em->flush();
    }

    public function removeItem(Order $cart, OrderItem $item): void
    {
        $cart->removeItem($item);
        $this->recalculate($cart);
        $this->em->flush();
    }

    public function updateItemQty(OrderItem $item, Order $cart, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeItem($cart, $item);

            return;
        }

        $item->setQuantity($qty);
        $this->recalculateItem($item);
        $this->recalculate($cart);
        $this->em->flush();
    }

    private function recalculateItem(OrderItem $item): void
    {
        $item->setTotal($item->getUnitPrice() * $item->getQuantity());
    }

    private function recalculate(Order $cart): void
    {
        $cart->recalculateItemsTotal();
    }
}
