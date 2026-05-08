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
 * Order-level fixed discount that works with flat variant pricing.
 * Sylius's built-in FixedDiscountPromotionActionCommand distributes
 * discounts per OrderItemUnit and requires ChannelPricing::getMinimumPrice(),
 * which this project does not implement.
 */
final class OrderFixedDiscountCommand implements PromotionActionCommandInterface
{
    public const TYPE = 'order_fixed_discount';

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

        $channel = $subject->getChannel();
        $channelCode = $channel?->getCode();
        if (null === $channelCode || !isset($configuration[$channelCode]['amount'])) {
            return false;
        }

        $configuredAmount = (int) $configuration[$channelCode]['amount'];
        if (0 === $configuredAmount) {
            return false;
        }

        $total = $subject->getPromotionSubjectTotal();
        if (0 === $total) {
            return false;
        }

        $amount = -1 * min($total, $configuredAmount);

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
