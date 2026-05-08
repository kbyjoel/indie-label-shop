<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Channel;
use App\Entity\Promotion;
use App\Entity\PromotionAction;
use App\Entity\PromotionCoupon;
use App\Entity\PromotionRule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Promotion fixtures covering every coupon/automatic promotion scenario:
 *
 * Automatic:
 *   AUTO-WELCOME10  — 10 % off all orders, no conditions
 *   AUTO-MIN50      — 5 € off when cart ≥ 50 € (item_total rule)
 *
 * Coupons — happy path:
 *   SUMMER20        — 20 % off, no conditions
 *   FLAT10          — 10 € flat discount, no conditions
 *   FREESHIP        — 100 % shipping discount
 *
 * Coupons — conditional (rules not met if cart is small):
 *   BIGSPEND        — 15 € off, requires cart ≥ 100 €
 *   WELCOME         — 15 % off first order only (nth_order rule)
 *
 * Coupons — not eligible (coupon-level limits):
 *   EXPIRED         — promotion ended yesterday
 *   USED_UP         — coupon usage limit exhausted
 *
 * Exclusive:
 *   EXCLUSIVE30     — 30 % off, exclusive (blocks other promotions)
 */
class PromotionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Channel|null $channel */
        $channel = $manager->getRepository(Channel::class)->findOneBy(['code' => 'WEB']);
        if (null === $channel) {
            throw new \RuntimeException('Channel WEB not found. Run AppFixtures first.');
        }

        $promotions = $this->definitions($channel);

        foreach ($promotions as $promotion) {
            $manager->persist($promotion);
        }

        $manager->flush();
    }

    // ---------------------------------------------------------------
    // Promotion definitions
    // ---------------------------------------------------------------

    /** @return Promotion[] */
    private function definitions(Channel $channel): array
    {
        return [
            $this->autoWelcome10($channel),
            $this->autoMin50($channel),
            $this->summer20($channel),
            $this->flat10($channel),
            $this->freeShipping($channel),
            $this->bigSpend($channel),
            $this->welcomeFirstOrder($channel),
            $this->expired($channel),
            $this->usedUp($channel),
            $this->exclusive30($channel),
        ];
    }

    // --- Automatic promotions ---

    private function autoWelcome10(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'AUTO-WELCOME10',
            name: '-10 % sur toute la boutique',
            couponBased: false,
            priority: 10,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.10]));

        return $promo;
    }

    private function autoMin50(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'AUTO-MIN50',
            name: '-5 € dès 50 € d\'achat',
            couponBased: false,
            priority: 5,
        );
        $promo->addChannel($channel);
        $promo->addRule($this->makeRule('item_total', ['WEB' => ['amount' => 5000]]));
        $promo->addAction($this->makeAction('order_fixed_discount', ['WEB' => ['amount' => 500]]));

        return $promo;
    }

    // --- Coupon: happy path ---

    private function summer20(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-SUMMER20',
            name: 'Summer 2025 — 20 % off',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.20]));
        $promo->addCoupon($this->makeCoupon('SUMMER20'));

        return $promo;
    }

    private function flat10(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-FLAT10',
            name: '-10 € sur votre commande',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_fixed_discount', ['WEB' => ['amount' => 1000]]));
        $promo->addCoupon($this->makeCoupon('FLAT10'));

        return $promo;
    }

    private function freeShipping(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-FREESHIP',
            name: 'Livraison offerte',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('shipping_percentage_discount', ['percentage' => 1.0]));
        $promo->addCoupon($this->makeCoupon('FREESHIP'));

        return $promo;
    }

    // --- Coupon: conditions (rules not met with a small cart) ---

    private function bigSpend(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-BIGSPEND',
            name: '-15 € dès 100 € d\'achat',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addRule($this->makeRule('item_total', ['WEB' => ['amount' => 10000]])); // 100 €
        $promo->addAction($this->makeAction('order_fixed_discount', ['WEB' => ['amount' => 1500]])); // -15 €
        $promo->addCoupon($this->makeCoupon('BIGSPEND'));

        return $promo;
    }

    private function welcomeFirstOrder(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-WELCOME',
            name: '-15 % sur votre première commande',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addRule($this->makeRule('nth_order', ['nth' => 1]));
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.15]));
        $promo->addCoupon($this->makeCoupon('WELCOME'));

        return $promo;
    }

    // --- Coupon: not eligible ---

    private function expired(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-EXPIRED',
            name: '-10 % (expiré)',
            couponBased: true,
            endsAt: new \DateTime('-1 day'),
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.10]));
        $promo->addCoupon($this->makeCoupon('EXPIRED'));

        return $promo;
    }

    private function usedUp(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-USEDUP',
            name: '-10 % (quota épuisé)',
            couponBased: true,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.10]));

        $coupon = $this->makeCoupon('USED_UP', usageLimit: 1);
        $coupon->setUsed(1); // already exhausted
        $promo->addCoupon($coupon);

        return $promo;
    }

    // --- Exclusive ---

    private function exclusive30(Channel $channel): Promotion
    {
        $promo = $this->makePromotion(
            code: 'PROMO-EXCLUSIVE30',
            name: '-30 % exclusif (bloque les autres promos)',
            couponBased: true,
            exclusive: true,
            priority: 100,
        );
        $promo->addChannel($channel);
        $promo->addAction($this->makeAction('order_percentage_discount', ['percentage' => 0.30]));
        $promo->addCoupon($this->makeCoupon('EXCLUSIVE30'));

        return $promo;
    }

    // ---------------------------------------------------------------
    // Factories
    // ---------------------------------------------------------------

    private function makePromotion(
        string $code,
        string $name,
        bool $couponBased = false,
        bool $exclusive = false,
        int $priority = 0,
        ?\DateTime $startsAt = null,
        ?\DateTime $endsAt = null,
    ): Promotion {
        $promo = new Promotion();
        $promo->setCode($code);
        $promo->setName($name);
        $promo->setCouponBased($couponBased);
        $promo->setExclusive($exclusive);
        $promo->setPriority($priority);
        $promo->setStartsAt($startsAt);
        $promo->setEndsAt($endsAt);

        return $promo;
    }

    /** @param array<string, mixed> $configuration */
    private function makeRule(string $type, array $configuration): PromotionRule
    {
        $rule = new PromotionRule();
        $rule->setType($type);
        $rule->setConfiguration($configuration);

        return $rule;
    }

    /** @param array<string, mixed> $configuration */
    private function makeAction(string $type, array $configuration): PromotionAction
    {
        $action = new PromotionAction();
        $action->setType($type);
        $action->setConfiguration($configuration);

        return $action;
    }

    private function makeCoupon(string $code, ?int $usageLimit = null): PromotionCoupon
    {
        $coupon = new PromotionCoupon();
        $coupon->setCode($code);
        $coupon->setUsageLimit($usageLimit);
        $coupon->setCreatedAt(new \DateTime());

        return $coupon;
    }
}
