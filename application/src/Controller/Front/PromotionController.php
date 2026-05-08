<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Entity\Adjustment;
use App\Entity\Order;
use App\Entity\PromotionCoupon;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Promotion\Processor\PromotionProcessorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'front_cart_promotion_')]
class PromotionController extends AbstractController
{
    public function __construct(
        private CartContext $cartContext,
        private EntityManagerInterface $em,
        #[Autowire(service: 'app.promotion_processor')]
        private PromotionProcessorInterface $promotionProcessor,
        #[Autowire(service: 'app.promotion_coupon_eligibility_checker')]
        private PromotionCouponEligibilityCheckerInterface $couponEligibilityChecker,
    ) {
    }

    #[Route('/apply-coupon', name: 'apply_coupon', methods: ['POST'])]
    public function applyCoupon(Request $request): JsonResponse
    {
        $order = $this->cartContext->getCart($request);

        $data = $request->toArray();
        $code = trim((string) ($data['coupon_code'] ?? ''));

        if ('' === $code) {
            return $this->json(['success' => false, 'error_code' => 'COUPON_NOT_FOUND', 'message' => "Ce code promo n'existe pas."], 422);
        }

        $coupon = $this->em->getRepository(PromotionCoupon::class)->findOneBy(['code' => $code]);
        if (null === $coupon) {
            return $this->json(['success' => false, 'error_code' => 'COUPON_NOT_FOUND', 'message' => "Ce code promo n'existe pas."], 422);
        }

        if (!$this->couponEligibilityChecker->isEligible($order, $coupon)) {
            return $this->json(['success' => false, 'error_code' => 'COUPON_NOT_ELIGIBLE', 'message' => "Ce code promo n'est plus valide."], 422);
        }

        $order->setPromotionCoupon($coupon);
        $this->promotionProcessor->process($order);

        $couponPromotion = $coupon->getPromotion();
        if (null === $couponPromotion || !$order->hasPromotion($couponPromotion)) {
            $order->setPromotionCoupon(null);
            $this->promotionProcessor->process($order);

            return $this->json(['success' => false, 'error_code' => 'PROMOTION_RULES_NOT_MET', 'message' => 'Votre panier ne remplit pas les conditions requises pour ce code promo.'], 422);
        }

        $this->em->flush();

        return $this->json(array_merge(['success' => true], $this->buildCartResponse($order)));
    }

    #[Route('/remove-coupon', name: 'remove_coupon', methods: ['DELETE'])]
    public function removeCoupon(Request $request): JsonResponse
    {
        $order = $this->cartContext->getCart($request);

        $order->setPromotionCoupon(null);
        $this->promotionProcessor->process($order);
        $this->em->flush();

        return $this->json(array_merge(['success' => true], $this->buildCartResponse($order)));
    }

    /** @return array<string, mixed> */
    private function buildCartResponse(Order $order): array
    {
        $couponCode = $order->getPromotionCoupon()?->getCode();
        $couponPromotionCode = $order->getPromotionCoupon()?->getPromotion()?->getCode();

        $adjustments = [];
        foreach ($order->getAdjustments(Adjustment::ORDER_PROMOTION_ADJUSTMENT) as $adj) {
            $adjustments[] = [
                'label' => $adj->getLabel(),
                'amount' => $adj->getAmount(),
                'removable' => $adj->getOriginCode() === $couponPromotionCode,
            ];
        }

        $promotionsTotal = array_sum(array_column($adjustments, 'amount'));

        return [
            'coupon_code' => $couponCode,
            'adjustments' => $adjustments,
            'items_total' => $order->getItemsTotal(),
            'promotions_total' => $promotionsTotal,
            'shipping_total' => $order->getAdjustmentsTotalRecursively(Adjustment::SHIPPING_ADJUSTMENT),
            'total' => $order->getTotal(),
        ];
    }
}
