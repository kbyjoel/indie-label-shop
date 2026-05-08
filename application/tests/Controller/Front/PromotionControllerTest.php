<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use App\Component\Cart\CartContext;
use App\Controller\Front\PromotionController;
use App\Entity\Adjustment;
use App\Entity\Order;
use App\Entity\PromotionCoupon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Processor\PromotionProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PromotionControllerTest extends TestCase
{
    private CartContext&MockObject $cartContext;
    private EntityManagerInterface&MockObject $em;
    private PromotionProcessorInterface&MockObject $processor;
    private PromotionCouponEligibilityCheckerInterface&MockObject $eligibilityChecker;
    private PromotionController $controller;

    protected function setUp(): void
    {
        $this->cartContext = $this->createMock(CartContext::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->processor = $this->createMock(PromotionProcessorInterface::class);
        $this->eligibilityChecker = $this->createMock(PromotionCouponEligibilityCheckerInterface::class);

        $this->controller = new PromotionController(
            $this->cartContext,
            $this->em,
            $this->processor,
            $this->eligibilityChecker,
        );

        // AbstractController::json() needs a container; stub it so it falls back to plain JsonResponse
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $this->controller->setContainer($container);
    }

    // --- applyCoupon ---

    public function testApplyCouponReturnsNotFoundWhenCodeIsEmpty(): void
    {
        $this->cartContext->method('getCart')->willReturn($this->makeOrder());

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => '']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertFalse($body['success']);
        self::assertSame('COUPON_NOT_FOUND', $body['error_code']);
    }

    public function testApplyCouponReturnsNotFoundWhenCodeIsWhitespaceOnly(): void
    {
        $this->cartContext->method('getCart')->willReturn($this->makeOrder());

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => '   ']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('COUPON_NOT_FOUND', $body['error_code']);
    }

    public function testApplyCouponReturnsNotFoundWhenCodeDoesNotExistInDatabase(): void
    {
        $this->cartContext->method('getCart')->willReturn($this->makeOrder());
        $this->mockRepo(null);
        $this->em->expects(self::never())->method('flush');

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'BADCODE']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('COUPON_NOT_FOUND', $body['error_code']);
    }

    public function testApplyCouponReturnsNotEligibleWhenEligibilityCheckFails(): void
    {
        $order = $this->makeOrder();
        $coupon = $this->makeCoupon('EXPIRED', null);

        $this->cartContext->method('getCart')->willReturn($order);
        $this->mockRepo($coupon);
        $this->eligibilityChecker->method('isEligible')->with($order, $coupon)->willReturn(false);
        $this->processor->expects(self::never())->method('process');
        $this->em->expects(self::never())->method('flush');

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'EXPIRED']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('COUPON_NOT_ELIGIBLE', $body['error_code']);
    }

    public function testApplyCouponReturnsRulesNotMetAndRollsBackWhenCouponHasNoLinkedPromotion(): void
    {
        $order = $this->makeOrder();
        $coupon = $this->makeCoupon('ORPHAN', null); // promotion is null

        $this->cartContext->method('getCart')->willReturn($order);
        $this->mockRepo($coupon);
        $this->eligibilityChecker->method('isEligible')->willReturn(true);
        // processor runs once to apply, once to rollback
        $this->processor->expects(self::exactly(2))->method('process')->with($order);
        $this->em->expects(self::never())->method('flush');

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'ORPHAN']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('PROMOTION_RULES_NOT_MET', $body['error_code']);
    }

    public function testApplyCouponReturnsRulesNotMetAndRollsBackWhenOrderRulesNotSatisfied(): void
    {
        $promotion = $this->createStub(PromotionInterface::class);
        $coupon = $this->makeCoupon('HIGHSPEND', $promotion);
        $order = $this->makeOrder(hasPromotion: false); // processor runs but promo never attaches

        $this->cartContext->method('getCart')->willReturn($order);
        $this->mockRepo($coupon);
        $this->eligibilityChecker->method('isEligible')->willReturn(true);
        $this->processor->expects(self::exactly(2))->method('process');
        $this->em->expects(self::never())->method('flush');

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'HIGHSPEND']));
        $body = $this->body($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('PROMOTION_RULES_NOT_MET', $body['error_code']);
    }

    public function testApplyCouponFlushesAndReturnsCartTotalsOnSuccess(): void
    {
        $promotion = $this->createStub(PromotionInterface::class);
        $promotion->method('getCode')->willReturn('PROMO-SUMMER');

        $coupon = $this->makeCoupon('SUMMER20', $promotion);
        $adjustment = $this->makeAdjustment('Remise été', -2000, 'PROMO-SUMMER');
        $order = $this->makeOrder(
            hasPromotion: true,
            coupon: $coupon,
            adjustments: [$adjustment],
            itemsTotal: 8000,
            total: 6000,
        );

        $this->cartContext->method('getCart')->willReturn($order);
        $this->mockRepo($coupon);
        $this->eligibilityChecker->method('isEligible')->willReturn(true);
        $this->processor->expects(self::once())->method('process');
        $this->em->expects(self::once())->method('flush');

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'SUMMER20']));
        $body = $this->body($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($body['success']);
        self::assertSame('SUMMER20', $body['coupon_code']);
        self::assertSame(8000, $body['items_total']);
        self::assertSame(6000, $body['total']);
        self::assertSame(-2000, $body['promotions_total']);
        self::assertCount(1, $body['adjustments']);
        self::assertSame('Remise été', $body['adjustments'][0]['label']);
        self::assertSame(-2000, $body['adjustments'][0]['amount']);
        self::assertTrue($body['adjustments'][0]['removable']); // originCode matches coupon promo code
    }

    public function testApplyCouponMarksAutomaticAdjustmentAsNotRemovable(): void
    {
        $promotion = $this->createStub(PromotionInterface::class);
        $promotion->method('getCode')->willReturn('PROMO-SUMMER');

        $coupon = $this->makeCoupon('SUMMER20', $promotion);
        // automatic promotion has a different originCode
        $autoAdj = $this->makeAdjustment('Remise automatique', -500, 'PROMO-AUTO');
        $couponAdj = $this->makeAdjustment('Remise coupon', -2000, 'PROMO-SUMMER');
        $order = $this->makeOrder(
            hasPromotion: true,
            coupon: $coupon,
            adjustments: [$autoAdj, $couponAdj],
            itemsTotal: 10000,
            total: 7500,
        );

        $this->cartContext->method('getCart')->willReturn($order);
        $this->mockRepo($coupon);
        $this->eligibilityChecker->method('isEligible')->willReturn(true);

        $response = $this->controller->applyCoupon($this->postJson(['coupon_code' => 'SUMMER20']));
        $body = $this->body($response);

        self::assertTrue($body['success']);
        self::assertFalse($body['adjustments'][0]['removable']); // automatic — not removable
        self::assertTrue($body['adjustments'][1]['removable']);  // coupon — removable
        self::assertSame(-2500, $body['promotions_total']);
    }

    // --- removeCoupon ---

    public function testRemoveCouponClearsCouponRunsProcessorAndFlushes(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects(self::once())->method('setPromotionCoupon')->with(null);
        $order->method('getPromotionCoupon')->willReturn(null);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());
        $order->method('getItemsTotal')->willReturn(10000);
        $order->method('getTotal')->willReturn(10000);
        $order->method('getAdjustmentsTotalRecursively')->willReturn(0);

        $this->cartContext->method('getCart')->willReturn($order);
        $this->processor->expects(self::once())->method('process')->with($order);
        $this->em->expects(self::once())->method('flush');

        $response = $this->controller->removeCoupon(new Request());
        $body = $this->body($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($body['success']);
        self::assertNull($body['coupon_code']);
        self::assertSame(0, $body['promotions_total']);
        self::assertSame(10000, $body['total']);
    }

    public function testRemoveCouponReturnsEmptyAdjustmentsWhenAllPromotionsCleared(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getPromotionCoupon')->willReturn(null);
        $order->method('getAdjustments')->willReturn(new ArrayCollection());
        $order->method('getItemsTotal')->willReturn(5000);
        $order->method('getTotal')->willReturn(5000);
        $order->method('getAdjustmentsTotalRecursively')->willReturn(0);

        $this->cartContext->method('getCart')->willReturn($order);

        $response = $this->controller->removeCoupon(new Request());
        $body = $this->body($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $body['adjustments']);
        self::assertSame(0, $body['promotions_total']);
    }

    public function testRemoveCouponKeepsAutomaticAdjustmentsAfterReprocess(): void
    {
        // After removing the coupon the processor re-applies automatic promotions.
        // The controller sees whatever the order returns post-process.
        $autoAdj = $this->makeAdjustment('Remise automatique', -300, 'PROMO-AUTO');
        $order = $this->createMock(Order::class);
        $order->method('getPromotionCoupon')->willReturn(null);
        $order->method('getAdjustments')->willReturn(new ArrayCollection([$autoAdj]));
        $order->method('getItemsTotal')->willReturn(5000);
        $order->method('getTotal')->willReturn(4700);
        $order->method('getAdjustmentsTotalRecursively')->willReturn(0);

        $this->cartContext->method('getCart')->willReturn($order);

        $response = $this->controller->removeCoupon(new Request());
        $body = $this->body($response);

        self::assertTrue($body['success']);
        self::assertNull($body['coupon_code']);
        self::assertCount(1, $body['adjustments']);
        self::assertFalse($body['adjustments'][0]['removable']); // no coupon → nothing is removable
        self::assertSame(-300, $body['promotions_total']);
        self::assertSame(4700, $body['total']);
    }

    // --- helpers ---

    private function postJson(array $data): Request
    {
        return Request::create('', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode($data));
    }

    /** @return array<string, mixed> */
    private function body(JsonResponse $response): array
    {
        return (array) json_decode((string) $response->getContent(), true);
    }

    private function mockRepo(?PromotionCoupon $coupon): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($coupon);
        $this->em->method('getRepository')->with(PromotionCoupon::class)->willReturn($repo);
    }

    /**
     * @param Adjustment[] $adjustments
     */
    private function makeOrder(
        bool $hasPromotion = true,
        ?PromotionCoupon $coupon = null,
        array $adjustments = [],
        int $itemsTotal = 10000,
        int $total = 10000,
    ): Order&MockObject {
        $order = $this->createMock(Order::class);
        $order->method('hasPromotion')->willReturn($hasPromotion);
        $order->method('getPromotionCoupon')->willReturn($coupon);
        $order->method('getAdjustments')->willReturn(new ArrayCollection($adjustments));
        $order->method('getItemsTotal')->willReturn($itemsTotal);
        $order->method('getTotal')->willReturn($total);
        $order->method('getAdjustmentsTotalRecursively')->willReturn(0);

        return $order;
    }

    private function makeCoupon(string $code, ?PromotionInterface $promotion): PromotionCoupon&Stub
    {
        $coupon = $this->createStub(PromotionCoupon::class);
        $coupon->method('getCode')->willReturn($code);
        $coupon->method('getPromotion')->willReturn($promotion);

        return $coupon;
    }

    private function makeAdjustment(string $label, int $amount, string $originCode): Adjustment&Stub
    {
        $adj = $this->createStub(Adjustment::class);
        $adj->method('getLabel')->willReturn($label);
        $adj->method('getAmount')->willReturn($amount);
        $adj->method('getOriginCode')->willReturn($originCode);

        return $adj;
    }
}
