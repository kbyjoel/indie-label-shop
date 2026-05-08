<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\Promotion;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Tests against the real Order entity to catch collection initialisation bugs
 * that mocked Order objects would silently hide.
 */
class OrderTest extends TestCase
{
    public function testPromotionsCollectionIsInitialisedOnConstruct(): void
    {
        $order = new Order();

        self::assertInstanceOf(Collection::class, $order->getPromotions());
        self::assertCount(0, $order->getPromotions());
    }

    public function testHasPromotionReturnsFalseWhenNotApplied(): void
    {
        $order = new Order();
        $promotion = new Promotion();

        self::assertFalse($order->hasPromotion($promotion));
    }

    public function testAddPromotionAndHasPromotion(): void
    {
        $order = new Order();
        $promotion = new Promotion();
        $promotion->setCode('PROMO-TEST');
        $promotion->setName('Test');

        $order->addPromotion($promotion);

        self::assertTrue($order->hasPromotion($promotion));
        self::assertCount(1, $order->getPromotions());
    }

    public function testRemovePromotionRemovesItFromCollection(): void
    {
        $order = new Order();
        $promotion = new Promotion();
        $promotion->setCode('PROMO-TEST');
        $promotion->setName('Test');

        $order->addPromotion($promotion);
        $order->removePromotion($promotion);

        self::assertFalse($order->hasPromotion($promotion));
        self::assertCount(0, $order->getPromotions());
    }
}
