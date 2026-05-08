<?php

declare(strict_types=1);

namespace App\Component\Promotion\Action;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Webmozart\Assert\Assert;

/**
 * Order-level percentage discount that works with flat variant pricing.
 * Sylius's built-in PercentageDiscountPromotionActionCommand distributes
 * discounts per OrderItemUnit and requires ChannelPricing::getMinimumPrice(),
 * which this project does not implement.
 */
final class OrderPercentageDiscountCommand implements PromotionActionCommandInterface
{
    public const TYPE = 'order_percentage_discount';

    /** @param AdjustmentFactoryInterface<\App\Entity\Adjustment> $adjustmentFactory */
    public function __construct(
        #[Autowire(service: 'app.adjustment_factory')]
        private AdjustmentFactoryInterface $adjustmentFactory,
    ) {
    }

    /** @param array<string, mixed> $configuration */
    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        /* @var OrderInterface $subject */
        Assert::isInstanceOf($subject, OrderInterface::class);

        $percentage = $configuration['percentage'] ?? null;
        if (!\is_float($percentage) && !\is_int($percentage)) {
            return false;
        }
        if ($percentage <= 0 || $percentage > 1) {
            return false;
        }

        $total = $subject->getPromotionSubjectTotal();
        if (0 === $total) {
            return false;
        }

        $amount = -1 * (int) round($total * (float) $percentage);
        if (0 === $amount) {
            return false;
        }

        $adjustment = $this->adjustmentFactory->createWithData(
            AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
            $promotion->getName() ?? $promotion->getCode() ?? '',
            $amount,
        );
        $adjustment->setOriginCode($promotion->getCode());
        $subject->addAdjustment($adjustment);

        return true;
    }

    /** @param array<string, mixed> $configuration */
    public function revert(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): void
    {
        /* @var OrderInterface $subject */
        Assert::isInstanceOf($subject, OrderInterface::class);

        foreach ($subject->getAdjustments(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT) as $adjustment) {
            if ($promotion->getCode() === $adjustment->getOriginCode()) {
                $subject->removeAdjustment($adjustment);
            }
        }
    }
}
