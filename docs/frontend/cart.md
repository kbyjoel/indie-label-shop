# Cart — Architecture & Developer Guide

## Overview

The cart is a fully custom implementation built on Sylius Core entities (`Order`, `OrderItem`, `ProductVariant`), without SyliusShopBundle. The session stores only a UUID token; the cart state lives in the database.

---

## Architecture

```
Session (_cart_token = UUID)
  └─► CartContext::getCart(Request)
        ├─ Finds Order[tokenValue, state=cart] in DB
        └─ Creates new Order if absent (sets channel, locale, currency)

CartController (POST) ─���� CartManager
                               ├─ addItem(Order, ProductVariant, qty)
                               ├─ removeItem(Order, OrderItem)
                               └─ updateItemQty(OrderItem, Order, qty)
                                     ��─ flush() via EntityManager
```

---

## `CartContext`

**File:** `src/Component/Cart/CartContext.php`

| Method | Description |
|--------|-------------|
| `getCart(Request): Order` | Returns the current cart (creates if absent, persists session token) |
| `getCartItemCount(Request): int` | Returns 0 without creating a cart if no session token exists |

The session key is `_cart_token`. A new `Order` is created with:
- `tokenValue`: UUID v4
- `state`: `Order::STATE_CART`
- `channel`: first enabled channel from DB
- `localeCode`: from `$request->getLocale()`
- `currencyCode`: from `$channel->getBaseCurrency()->getCode()`

---

## `CartManager`

**File:** `src/Component/Cart/CartManager.php`

| Method | Description |
|--------|-------------|
| `addItem(Order, ProductVariant, int qty=1)` | Increments quantity if variant already in cart, otherwise creates a new `OrderItem` |
| `removeItem(Order, OrderItem)` | Removes item from cart, recalculates total |
| `updateItemQty(OrderItem, Order, int)` | Updates quantity; delegates to `removeItem()` if qty ≤ 0 |

The `unitPrice` is copied from `ProductVariant::getPrice()` at add time (price is locked in the cart).

---

## Cart API endpoints

| Route | Method | Body | Response |
|-------|--------|------|----------|
| `/panier` | GET | — | HTML |
| `/panier/ajouter` | POST | `{variantId, qty}` JSON | `{success, cartCount, message}` |
| `/panier/retirer/{itemId}` | POST | — | `{success, cartCount}` |
| `/panier/modifier/{itemId}` | POST | `{qty}` JSON | `{success, cartCount, itemTotal, cartTotal}` |

Security: `remove` and `update` verify that the `OrderItem` belongs to the current session's cart.

---

## Twig extension — `cart_item_count()`

**File:** `src/Twig/CartExtension.php`

Provides a `cart_item_count()` Twig function. Returns 0 without side effects if no session token exists (does **not** create a cart). Used in `_header.html.twig` to render the initial badge count.

---

## Stimulus controllers

### `product_variant_controller.js`

Attached to the variant selector area on the product show page.

**Values:**
- `variants`: JSON array `[{id, price, stock, optionValueIds: [...]}]`

**Targets:** `optionSelect`, `priceDisplay`, `addButton`, `variantIdInput`

**Behaviour:**
- On option `change`: finds the matching variant by cross-matching selected `optionValueIds`
- Updates price display and disables the add-to-cart button if variant is null or out of stock

### `cart_controller.js`

Handles cart interactions: add from product page, remove and update from cart page, badge count updates.

**Values:**
- `count` (Number): initial count (server-rendered via `cart_item_count()`)
- `addUrl` (String): URL for POST add action (passed via `data-cart-add-url-value`)

**Targets:** `countBadge`, `form`, `submitButton`, `feedback`

Cart page items pass URLs directly as data attributes:
- `data-remove-url="{{ path('front_cart_remove', {itemId: item.id}) }}"`
- `data-update-url="{{ path('front_cart_update', {itemId: item.id}) }}"`

---

## `PurgeExpiredCartsCommand`

**File:** `src/Command/PurgeExpiredCartsCommand.php`  
**Command:** `app:cart:purge-expired`

Deletes `Order` entities with `state = cart` and `updatedAt` older than `%app.cart.expiration_days%` (default: 14 days, configured in `config/services.yaml`).

Schedule as a daily cron:

```bash
0 3 * * * php /var/www/bin/console app:cart:purge-expired
```

---

## Product image

`ProductImage` entity (`src/Entity/ProductImage.php`) follows the same pattern as `AlbumImage`:
- Table: `indie_product_image`
- `OneToOne` on `Product::$image`
- Uses `AttachedImage` from aropixel/admin-bundle
- LiipImagine filter sets: `product_card` (400×400 outbound) and `product_image` (800×800 inset)

---

## Testing in development

1. Create a merch product in admin with at least one variant (set slug in translation)
2. Open `/boutique` → product card visible
3. Open `/produit/{slug}` → if multiple options, selectors appear
4. Click "Ajouter au panier" → cart badge in header increments
5. Open `/panier` → item listed with correct subtotal
6. Change quantity �� subtotal updates, no page reload
7. Click remove → item disappears, total updates
8. Add same variant twice → quantity accumulates (one line)
9. `castor docker:builder -- php bin/console app:cart:purge-expired` → exits successfully
