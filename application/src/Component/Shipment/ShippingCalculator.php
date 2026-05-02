<?php

declare(strict_types=1);

namespace App\Component\Shipment;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ShippingMethod;
use App\Repository\ShippingMethodRepository;

/**
 * Resolves which shipping methods are eligible for a cart and computes their amount.
 *
 * Calculators:
 *   flat_rate      — fixed amount regardless of weight/units
 *   per_unit_rate  — amount × total item quantity
 *   weight_range   — amount depends on total cart weight (bracket lookup)
 *
 * Zone matching — exclusive / most-specific-wins:
 *   Among all enabled methods, we first find the smallest zone (fewest members)
 *   that contains the shipping country. Only methods from that zone are eligible.
 *   If no zone explicitly lists the country, methods from catch-all zones (empty
 *   members, e.g. "WORLD") are shown instead. This prevents a French customer
 *   from seeing EU or worldwide shipping options.
 *
 * Weight eligibility:
 *   flat_rate / per_unit_rate — checked via ShippingMethodRule entries
 *   weight_range              — cart weight must fall within at least one bracket
 */
class ShippingCalculator
{
    public function __construct(private ShippingMethodRepository $repository)
    {
    }

    /** @return ShippingMethod[] */
    public function getEligibleMethods(Order $cart): array
    {
        $countryCode = $cart->getShippingAddress()?->getCountryCode() ?? '';
        $weight = $this->computeCartWeight($cart);
        $allMethods = $this->repository->findBy(['enabled' => true], ['position' => 'ASC']);
        $targetZoneSize = $this->findMostSpecificZoneSize($allMethods, $countryCode);

        return array_values(array_filter(
            $allMethods,
            fn (ShippingMethod $m) => $this->isEligible($m, $countryCode, $weight, $targetZoneSize),
        ));
    }

    public function computeAmount(ShippingMethod $method, Order $cart): int
    {
        $weight = $this->computeCartWeight($cart);

        return match ($method->getCalculator()) {
            'per_unit_rate' => $this->resolveFlatAmount($method, $cart) * $this->computeUnitCount($cart),
            'weight_range' => $this->resolveWeightRangeAmount($method, $weight),
            default => $this->resolveFlatAmount($method, $cart), // flat_rate + unknown
        };
    }

    /**
     * @param ShippingMethod[] $methods
     * @return int|null Minimum zone member count among zones that explicitly list
     *                  the country, or null if no such zone exists (→ use catch-all)
     */
    private function findMostSpecificZoneSize(array $methods, string $countryCode): ?int
    {
        if ($countryCode === '') {
            return null;
        }

        $minSize = null;
        foreach ($methods as $method) {
            $zone = $method->getZone();
            if ($zone === null) {
                continue;
            }
            $members = $zone->getMembers();
            if ($members->isEmpty()) {
                continue; // skip catch-all zones in this pass
            }
            foreach ($members as $member) {
                if ($member->getCode() === $countryCode) {
                    $size = $members->count();
                    if ($minSize === null || $size < $minSize) {
                        $minSize = $size;
                    }
                    break;
                }
            }
        }

        return $minSize;
    }

    private function isEligible(ShippingMethod $method, string $countryCode, float $weight, ?int $targetZoneSize): bool
    {
        if (!$this->isZoneEligible($method, $countryCode, $targetZoneSize)) {
            return false;
        }

        if ('weight_range' === $method->getCalculator()) {
            return $this->isWeightRangeEligible($method, $weight);
        }

        return $this->isRulesEligible($method, $weight);
    }

    /**
     * Exclusive zone check: a method is eligible only if its zone is the most
     * specific one for the country (same member count and contains the country),
     * or a catch-all when no specific zone matches.
     */
    private function isZoneEligible(ShippingMethod $method, string $countryCode, ?int $targetZoneSize): bool
    {
        if ($countryCode === '') {
            return true;
        }

        $zone = $method->getZone();
        if ($zone === null) {
            return true;
        }

        $members = $zone->getMembers();

        if ($targetZoneSize === null) {
            // No zone explicitly lists this country → only catch-all methods apply
            return $members->isEmpty();
        }

        // Show only the most specific matching zone (same member count + contains country)
        if ($members->count() !== $targetZoneSize) {
            return false;
        }

        foreach ($members as $member) {
            if ($member->getCode() === $countryCode) {
                return true;
            }
        }

        return false;
    }

    private function isRulesEligible(ShippingMethod $method, float $weight): bool
    {
        foreach ($method->getRules() as $rule) {
            $config = $rule->getConfiguration();
            $ok = match ($rule->getType()) {
                'total_weight_greater_than_or_equal' => $weight >= (float) ($config['weight'] ?? 0),
                'total_weight_less_than_or_equal' => $weight <= (float) ($config['weight'] ?? \PHP_FLOAT_MAX),
                default => true,
            };
            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    private function isWeightRangeEligible(ShippingMethod $method, float $weight): bool
    {
        foreach ($method->getConfiguration()['brackets'] ?? [] as $bracket) {
            $min = (float) ($bracket['min'] ?? 0);
            $max = isset($bracket['max']) ? (float) $bracket['max'] : null;
            if ($weight >= $min && ($max === null || $weight <= $max)) {
                return true;
            }
        }

        return false;
    }

    private function resolveFlatAmount(ShippingMethod $method, Order $cart): int
    {
        $config = $method->getConfiguration();

        // Direct amount (fixture / legacy format)
        if (isset($config['amount'])) {
            return (int) $config['amount'];
        }

        // Per-channel format — prefer the cart's channel
        $channelCode = $cart->getChannel()?->getCode();
        if ($channelCode !== null && isset($config[$channelCode]['amount'])) {
            return (int) $config[$channelCode]['amount'];
        }

        // Fallback: first channel found
        foreach ($config as $value) {
            if (\is_array($value) && isset($value['amount'])) {
                return (int) $value['amount'];
            }
        }

        return 0;
    }

    private function resolveWeightRangeAmount(ShippingMethod $method, float $weight): int
    {
        foreach ($method->getConfiguration()['brackets'] ?? [] as $bracket) {
            $min = (float) ($bracket['min'] ?? 0);
            $max = isset($bracket['max']) ? (float) $bracket['max'] : null;
            if ($weight >= $min && ($max === null || $weight <= $max)) {
                return (int) ($bracket['amount'] ?? 0);
            }
        }

        return 0;
    }

    private function computeCartWeight(Order $cart): float
    {
        $weight = 0.0;
        foreach ($cart->getItems() as $item) {
            /** @var OrderItem $item */
            $variant = $item->getVariant();
            if ($variant !== null) {
                $weight += ($variant->getShippingWeight() ?? 0.0) * $item->getQuantity();
            }
        }

        return $weight;
    }

    private function computeUnitCount(Order $cart): int
    {
        $count = 0;
        foreach ($cart->getItems() as $item) {
            $count += $item->getQuantity();
        }

        return $count;
    }
}
