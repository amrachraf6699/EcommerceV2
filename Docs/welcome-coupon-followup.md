# Welcome Coupon Follow-Up

This document lists the remaining work that was intentionally not implemented yet because the current repo does not have a real storefront checkout / coupon redemption flow.

## Current Status

Already implemented:

- Marketing settings were changed from mystery-box to welcome-coupon controls.
- Homepage popup now collects an email and issues a personal welcome coupon.
- Coupon records are stored in `welcome_coupons`.
- Coupon emails are sent through `WelcomeCouponIssuedNotification`.
- Coupon ownership is enforced in code through `WelcomeCouponService::findRedeemableForCustomer(...)`.

Not implemented yet:

- A real storefront checkout page / cart page where users can enter a coupon code.
- Applying the coupon to cart totals and order totals.
- Marking a coupon as used when an order is successfully placed.
- Admin UI to inspect issued welcome coupons.

## Recommended Next Steps

### 1. Build storefront cart + checkout coupon entry

Add a real customer-facing flow where the user can:

- view current cart items
- sign in or identify their customer account
- enter a coupon code
- see discount preview before placing the order

Recommended additions:

- `Frontend\CartController`
- `Frontend\CheckoutController`
- frontend views for cart and checkout
- routes for showing cart, updating cart, checkout form, and order placement

### 2. Add coupon validation endpoint/service usage

Use the existing `WelcomeCouponService::findRedeemableForCustomer(...)` during checkout.

Expected behavior:

- require a logged-in `customer` before coupon redemption
- load coupon by code
- reject if code does not belong to that customer/email
- reject if `used_at` is already set
- accept if code belongs to that account and is unused

Recommended implementation:

- add a checkout-level service, for example `CheckoutPricingService`
- call `findRedeemableForCustomer(...)`
- calculate discount amount based on:
  - `discount_type = percent`
  - `discount_type = amount`

### 3. Apply discount to order totals

When placing an order:

- compute cart subtotal
- compute coupon discount
- store discount in `orders.discount_total`
- reduce `grand_total` accordingly

Recommended rule order:

1. subtotal from cart items
2. coupon discount
3. shipping
4. tax/VAT
5. final grand total

If your business rules differ, decide and keep the order consistent across UI and backend.

### 4. Mark coupon as used

After successful order creation:

- set `welcome_coupons.used_at = now()`
- optionally store `order_id` on the coupon table for traceability

Recommended schema follow-up:

- add nullable `order_id` foreign key to `welcome_coupons`

That makes support/admin tracking much easier later.

### 5. Add admin coupon management/reporting

Recommended admin features:

- list issued welcome coupons
- filter by email, code, used/unused
- show discount type/value
- show sent date and used date
- optionally show linked customer and linked order

Suggested files/modules:

- `Admin\WelcomeCouponController`
- `resources/views/admin/welcome-coupons/*`
- admin route group under something like `admin/welcome-coupons`

### 6. Add customer authentication flow if needed

Right now the coupon can be issued to any email and linked later to a matching customer.

For stricter enforcement in production, add real storefront customer auth:

- register
- login
- forgot password
- profile/account page

Then coupon redemption can be limited to authenticated customers only.

### 7. Add tests for checkout redemption

When checkout exists, add tests for:

- correct customer can redeem own coupon
- different customer cannot redeem another customer’s coupon
- used coupon cannot be redeemed again
- percent discount calculates correctly
- amount discount calculates correctly
- random-issued discounts are preserved and not recalculated at redemption time
- order placement marks coupon as used
- `orders.discount_total` and `grand_total` are correct

## Suggested Minimal Phase 2 Scope

If you want the smallest useful next implementation, do this first:

1. build a basic cart/checkout page
2. require customer login before checkout
3. add coupon code input
4. validate via `WelcomeCouponService`
5. apply discount to `orders.discount_total`
6. mark coupon `used_at` after order success

That gives you a complete working welcome-coupon lifecycle without needing broader marketing/admin reporting features first.
