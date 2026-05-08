# Promotions — Frontend Integration

Covers automatic promotions and coupon-based promotions: service wiring, frontend endpoints, Stimulus controller, and how to extend the system.

---

## Architecture

The Sylius promotion pipeline is wired manually in `config/services/promotion.yaml` (no SyliusCoreBundle active). The promotion pipeline is:

```
CartManager.recalculate()
  └── PromotionProcessor.process($order)
        ├── PreQualifiedPromotionsProvider  → fetches active promos for the channel
        ├── CompositePromotionEligibilityChecker
        │     ├── PromotionDurationEligibilityChecker
        │     ├── PromotionUsageLimitEligibilityChecker
        │     ├── PromotionRulesEligibilityChecker  → dispatches to rule checkers
        │     └── PromotionSubjectCouponEligibilityChecker
        └── PromotionApplicator
              └── PromotionActionCommandRegistry  → dispatches to action commands
```

### Registered rule checkers

| Service ID | Sylius type key | Class |
|---|---|---|
| `app.promotion_rule.cart_quantity` | `cart_quantity` | `CartQuantityRuleChecker` |
| `app.promotion_rule.item_total` | `item_total` | `ItemTotalRuleChecker` |
| `app.promotion_rule.contains_product` | `contains_product` | `ContainsProductRuleChecker` |
| `app.promotion_rule.customer_group` | `customer_group` | `CustomerGroupRuleChecker` |
| `app.promotion_rule.nth_order` | `nth_order` | `NthOrderRuleChecker` |
| `app.promotion_rule.total_of_items_from_taxon` | `total_of_items_from_taxon` | `TotalOfItemsFromTaxonRuleChecker` |

### Registered action commands

| Service ID | Sylius type key | Class |
|---|---|---|
| `app.promotion_action.fixed_discount` | `order_fixed_discount` | `FixedDiscountPromotionActionCommand` |
| `app.promotion_action.percentage_discount` | `order_percentage_discount` | `PercentageDiscountPromotionActionCommand` |
| `app.promotion_action.unit_fixed_discount` | `unit_fixed_discount` | `UnitFixedDiscountPromotionActionCommand` |
| `app.promotion_action.unit_percentage_discount` | `unit_percentage_discount` | `UnitPercentageDiscountPromotionActionCommand` |
| `app.promotion_action.shipping_percentage_discount` | `shipping_percentage_discount` | `ShippingPercentageDiscountPromotionActionCommand` |

### AdjustmentFactory chain

Unit-level actions need `AdjustmentFactoryInterface`. The wiring is:

```
app.adjustment_simple_factory  → Sylius\Resource\Factory\Factory('App\Entity\Adjustment')
app.adjustment_factory         → Sylius\Component\Order\Factory\AdjustmentFactory(app.adjustment_simple_factory)
```

### PassthroughFilter

`UnitFixedDiscountPromotionActionCommand` and `UnitPercentageDiscountPromotionActionCommand` require three filter services (price range, taxon, product). The price range filter (`PriceRangeFilter`) in turn requires `ProductVariantPricesCalculatorInterface`, which is expensive to wire and never configured in the admin.

`App\Component\Promotion\Filter\PassthroughFilter` is used in place of `PriceRangeFilter`. It implements `FilterInterface` and returns all items unchanged — safe because price-range filtering is never configured via the admin UI.

---

## Automatic vs coupon promotions

| | Automatic | Coupon-based |
|---|---|---|
| `couponBased` flag | `false` | `true` |
| Trigger | Applied on every cart recalculate | Applied when customer enters a code |
| Removal | Removed automatically when rules no longer match | Removed via "×" button or `DELETE /cart/remove-coupon` |
| Admin field | No coupon section | Coupon section visible |

Both types are re-evaluated on every `CartManager.recalculate()` call (quantity change, item add/remove). This means:
- Automatic promotions appear/disappear as cart contents change
- An active coupon is also re-checked: if it no longer qualifies, it is removed by the processor

---

## Admin configuration format

Rule and action configuration is stored as JSON in `promotion_rule.configuration` / `promotion_action.configuration`. The formats expected by Sylius rule checkers and action commands are:

### Rules

| Type | Configuration format |
|---|---|
| `cart_quantity` | `{ "count": 3 }` |
| `item_total` | `{ "WEB": { "amount": 5000 } }` (amount in cents, channel-keyed) |
| `contains_product` | `{ "product_code": "ALBUM-001" }` |
| `customer_group` | `{ "group_code": "vip" }` |
| `nth_order` | `{ "nth": 2 }` |
| `total_of_items_from_taxon` | `{ "WEB": { "taxon": "vinyl", "amount": 3000 } }` |

### Actions

| Type | Configuration format |
|---|---|
| `order_fixed_discount` | `{ "WEB": { "amount": 1000 } }` (amount in cents, channel-keyed) |
| `order_percentage_discount` | `{ "percentage": 0.1 }` (0.1 = 10%) |
| `unit_fixed_discount` | `{ "WEB": { "amount": 500 } }` |
| `unit_percentage_discount` | `{ "WEB": { "percentage": 0.15 } }` |
| `shipping_percentage_discount` | `{ "percentage": 1.0 }` (1.0 = free shipping) |

---

## Frontend endpoints

Both routes live under `#[Route('/cart', name: 'front_cart_promotion_')]` in `src/Controller/Front/PromotionController.php`.

### `POST /cart/apply-coupon`

Request body (JSON):
```json
{ "coupon_code": "SUMMER20" }
```

Success `200`:
```json
{
  "success": true,
  "coupon_code": "SUMMER20",
  "adjustments": [
    { "label": "Summer sale", "amount": -2000, "removable": true }
  ],
  "items_total": 8000,
  "promotions_total": -2000,
  "shipping_total": 500,
  "total": 6500
}
```

Error `422`:
```json
{ "success": false, "error_code": "COUPON_NOT_FOUND", "message": "..." }
```

Error codes: `COUPON_NOT_FOUND`, `COUPON_NOT_ELIGIBLE`, `PROMOTION_RULES_NOT_MET`

### `DELETE /cart/remove-coupon`

No request body. Returns same JSON shape as above (with `coupon_code: null`, no removable adjustments). Automatic promotions that still qualify are re-applied.

---

## Stimulus controller

`assets/controllers/coupon_controller.js` — mounted on the coupon section via `data-controller="coupon"`.

### Targets

| Target | Element |
|---|---|
| `codeInput` | Text input for the coupon code |
| `applyButton` | Submit button (disabled during fetch) |
| `activeZone` | Div shown when a coupon is active |
| `activeCode` | `<span>` displaying the active code |
| `errorZone` | Error message paragraph |
| `adjustmentsList` | `<table>` that lists promotion adjustments |
| `promotionsTotal` | `<span>` showing the total discount |
| `cartTotal` | `<span>` showing the grand total |

### Values

| Value | Purpose |
|---|---|
| `applyUrl` | URL for `POST /cart/apply-coupon` |
| `removeUrl` | URL for `DELETE /cart/remove-coupon` |

### Key methods

- `applyCoupon(event)` — submits the form, calls `updateTotals(data)` on success or `showError(msg)` on failure
- `removeCoupon(event)` — removes the coupon, calls `updateTotals(data)`
- `updateTotals(data)` — re-renders the adjustments table, shows/hides the active zone and discount row, updates totals

The `renderAdjustment(adj)` helper generates a table row; when `adj.removable` is true it includes a "×" button wired to `coupon#removeCoupon`.

---

## Adding a new rule type

1. Choose a Sylius built-in rule checker class from `sylius/promotion/src/Checker/Rule/`.
2. Add a service definition in `config/services/promotion.yaml`:
   ```yaml
   app.promotion_rule.my_rule:
       class: Sylius\Component\Promotion\Checker\Rule\MyRuleChecker
       # constructor arguments if needed
   ```
3. Register it in the registry (still in `promotion.yaml`):
   ```yaml
   app.promotion_rule_checker_registry:
       calls:
           - register: ['my_rule', '@app.promotion_rule.my_rule']
   ```
4. Add the type to `PromotionRuleType::$types` in `src/Form/Admin/PromotionRuleType.php` and add the corresponding form fields.
5. Handle the configuration mapping in `PromotionController::applyRulesConfiguration()` in `src/Controller/Admin/PromotionController.php`.
6. Document the configuration format in the table above.

## Adding a new action type

1. Choose a Sylius built-in action command class from `sylius/promotion/src/Action/`.
2. Add a service definition + `calls: register:` entry in `config/services/promotion.yaml` (same pattern as rule types).
3. Add the type to `PromotionActionType::$types` and add form fields.
4. Handle the configuration mapping in `PromotionController::applyActionsConfiguration()`.
5. Document the configuration format in the table above.

---

## Manual test scenarios

1. **Automatic promotion — no rule**: create a promo without rules → open cart → discount line appears without any input.
2. **Automatic promotion — item_total rule**: min 50 € → cart < 50 € gives no discount; cart ≥ 50 € shows discount.
3. **Coupon — valid code**: enter `SUMMER20` → discount line appears + "Code applied" zone with "×" button.
4. **Coupon — invalid code**: enter `BADCODE` → inline error, no change to totals.
5. **Coupon — conditions not met**: coupon exists but cart doesn't satisfy promotion rules → `PROMOTION_RULES_NOT_MET` error.
6. **Coupon + automatic promotion simultaneously**: two discount lines appear, total reflects both.
7. **Exclusive promotion**: only one discount shown even when two promos are eligible (check `exclusive` flag on promotion).
8. **Coupon removal**: click "×" → coupon discount disappears; automatic promotions that still qualify remain.
9. **Checkout summary**: navigate through address → shipping → payment steps → discount line visible in the order summary at each step.

---

## Relevant files

| File | Role |
|---|---|
| `config/services/promotion.yaml` | Full Sylius promotion service graph |
| `src/Component/Promotion/Filter/PassthroughFilter.php` | No-op filter replacing PriceRangeFilter |
| `src/Repository/PromotionRepository.php` | Implements `PromotionRepositoryInterface` for `ActivePromotionsByChannelProvider` |
| `src/Component/Cart/CartManager.php` | Calls `promotionProcessor->process()` on every recalculate |
| `src/Controller/Front/PromotionController.php` | `apply-coupon` and `remove-coupon` endpoints |
| `assets/controllers/coupon_controller.js` | Stimulus controller for coupon form + totals updates |
| `templates/front/cart/index.html.twig` | Cart page with coupon form and discount rows |
| `templates/front/checkout/_summary.html.twig` | Checkout sidebar with read-only discount rows |
| `src/Controller/Admin/PromotionController.php` | Admin CRUD — maps form data to Sylius config format |
| `src/Form/Admin/PromotionRuleType.php` | Admin rule form |
| `src/Form/Admin/PromotionActionType.php` | Admin action form |
