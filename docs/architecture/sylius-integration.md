# Sylius Integration — Architecture Decisions

This document explains where the project intentionally deviates from the standard Sylius 2.2 pipeline, and why. The goal is for a future maintainer to understand the reasoning before changing anything.

---

## 1. Flat pricing model (no `ChannelPricing`)

### Context

Sylius models prices through a `ChannelPricing` entity linked to `ProductVariant`. This allows different prices per channel, a base currency, an original (strikethrough) price, a minimum price after discount, etc. The order processing pipeline (`OrderPricesRecalculator`, promotion action commands) relies entirely on `ChannelPricing`.

### Decision

A `price` column (integer, cents) is stored directly on `ProductVariant`. `ChannelPricing` is not mapped.

### Why

This project has a single channel (`WEB`) and a single currency (`EUR`). The complexity of `ChannelPricing` — a dedicated entity, a per-variant collection, a data migration, per-channel price management in the admin — provides no operational value in this context.

### Consequences

- `OrderPricesRecalculator` is not wired: prices are frozen at the time an item is added to the cart. Variant price changes do not propagate to open carts (acceptable behaviour).
- `ProductVariantPricesCalculator` is not wired for the same reason.
- Sylius's built-in promotion action commands (see section 3) cannot be used as-is.

### Upgrade path

Introducing `ChannelPricing` would require: mapping the entity, a schema migration, a data migration for all existing variants, updating the price management admin, updating `CartManager::addItem()`, and wiring `ProductVariantPriceCalculator` + `OrderPricesRecalculator`. The right time to do this is if the project moves to multiple channels or multiple currencies.

---

## 2. Cart management (`CartContext` + `CartManager`)

### Context

Sylius provides `ShopBasedCartContext`, which triggers a resolution chain: `CartContextInterface` → `ShopperContextInterface` → `SecurityContext` / `CustomerContext` / `ChannelContext`. Full order processing goes through `CompositeOrderProcessor`, which chains `OrderAdjustmentsClearer`, `OrderPricesRecalculator`, `PromotionProcessor`, `OrderTaxesProcessor`, and `OrderShipmentProcessor`.

### Decision

Two simple custom classes:

**`CartContext`** (`src/Component/Cart/CartContext.php`): resolves the current cart via session (key `_cart_token`). Creates a new cart if none exists. No `ShopperContextInterface`.

**`CartManager`** (`src/Component/Cart/CartManager.php`): handles `addItem`, `removeItem`, `updateItemQty`. Calls `$cart->recalculateItemsTotal()` then `$promotionProcessor->process($cart)` after each mutation.

### Why

`ShopBasedCartContext` requires `ShopperContextInterface`, which pulls in `SecurityContext`, `CustomerContext`, `ChannelContext` and their own dependencies. This project has custom authentication (`ShopUser`) and a single channel — wiring that full stack would only add adapters with no benefit.

The full `CompositeOrderProcessor` is incompatible: `OrderPricesRecalculator` and `OrderTaxesProcessor` both depend on `ChannelPricing` (see section 1). Only `PromotionProcessor` is used — the sole processor needed in our model.

### Consequences

- After `promotionProcessor->process()`, order-level adjustments automatically trigger `recalculateAdjustmentsTotal()` → `recalculateTotal()` via `Order::addAdjustment()`. No additional call in `CartManager` is needed.
- Tax is not recalculated dynamically (out of scope for now).

---

## 3. Custom promotion action commands

### Context

Sylius provides two action commands for order-level discounts:

- `PercentageDiscountPromotionActionCommand` (`order_percentage_discount`)
- `FixedDiscountPromotionActionCommand` (`order_fixed_discount`)

Both distribute the discount **per `OrderItemUnit`** via `UnitsPromotionAdjustmentsApplicator`, which calls `$variant->getChannelPricingForChannel($channel)->getMinimumPrice()` to cap the discount per unit.

### Decision

Two custom classes in `src/Component/Promotion/Action/`:

- `OrderPercentageDiscountCommand`: applies a single `ORDER_PROMOTION_ADJUSTMENT` directly on the `Order`
- `OrderFixedDiscountCommand`: same for a fixed discount (amount configured per channel code `WEB`)

Sylius's `ShippingPercentageDiscountPromotionActionCommand` is kept as-is (no `ChannelPricing` dependency).

The `unit_fixed_discount` and `unit_percentage_discount` types are removed from the admin form and the action registry (they require `OrderItemUnit` objects that this project does not create).

### Why

`ChannelPricing` is not mapped (section 1). Calling `getChannelPricingForChannel()` throws a fatal error. Adding an adjustment directly to the `Order` is Sylius's native mechanism for order-level discounts — it is what Sylius itself does via `Order::addAdjustment()`.

### Consequences

- No per-unit `minimumPrice` enforcement: a discount could theoretically reduce an item's price to zero (acceptable for this project).
- The total is always consistent: `Order::addAdjustment()` → `recalculateAdjustmentsTotal()` → `recalculateTotal()` cascade automatically.
- `unit_fixed_discount` / `unit_percentage_discount` promotion types are no longer available in the admin.

---

## 4. Sylius rule checkers — active scope

| Type | Status | Reason |
|---|---|---|
| `cart_quantity` | ✅ Active | No external dependencies |
| `item_total` | ✅ Active | No external dependencies |
| `contains_product` | ✅ Active | Reads the product code, not the ID |
| `customer_group` | ✅ Active | No external dependencies |
| `nth_order` | ✅ Active | Requires `OrderRepositoryInterface` → `App\Repository\OrderRepository` |
| `total_of_items_from_taxon` | ❌ Removed | Requires `TaxonRepositoryInterface`; no `Taxon` entity in this project |

`App\Repository\OrderRepository` implements Sylius's `OrderRepositoryInterface`. Only `countByCustomer()` and `countByCustomerAndCoupon()` have a real DQL implementation (filtering `state != 'cart'`). All other interface methods throw `RuntimeException('Not implemented')`.

---

## 5. `Order::$promotions` — explicitly mapped collection

### Context

Sylius declares the `ManyToMany` relationship between `Order` and `Promotion` in the base model, but the ORM mapping must be redeclared in the application's concrete class. Doctrine bypasses `__construct()` when hydrating from the database — it writes directly into properties via reflection. If the `$promotions` property is not mapped, it stays `null` after loading from the DB.

### Decision

`App\Entity\Order` explicitly redeclares the `#[ORM\ManyToMany]` mapping on `$promotions` and initialises it in `__construct()` with `new ArrayCollection()`.

### Why

The bug `Order::getPromotions(): Return value must be of type Collection, null returned` was caught at runtime (not by unit tests, which mocked `Order`). The tests in `tests/Entity/OrderTest.php` test the real entity class to prevent any regression of this kind.

### Lesson

Always test extended Sylius entities with the real class (no mock) for behaviours tied to collection initialisation.
