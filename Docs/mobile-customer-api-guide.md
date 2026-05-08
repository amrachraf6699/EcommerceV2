# Mobile Customer API Guide

## Summary
This API exposes the customer-facing mobile backend under `/api/v1`.

It covers:
- customer auth with Sanctum bearer tokens
- home/catalog/category/product browsing
- customer profile and address management
- customer-owned carts
- checkout summary and checkout initialization
- Tap payment status sync
- orders list, order details, and public order tracking

The mobile cart is **user-bound only**. Guest carts are not used in the mobile API.

## Base URL
Set your API base URL to your Laravel host:

```text
https://your-domain.com/api/v1
```

For local development:

```text
http://127.0.0.1:8000/api/v1
```

## Authentication
The API uses Laravel Sanctum personal access tokens.

1. Register or log in with:
   - `POST /auth/register`
   - `POST /auth/login`
2. Read the returned `token`.
3. Send it on protected requests:

```http
Authorization: Bearer <token>
Accept: application/json
```

Protected endpoints include:
- `/auth/me`
- `/profile`
- `/addresses`
- `/cart`
- `/checkout/*`
- `/orders`

## Main Endpoint Groups

### Public
- `GET /home`
- `GET /catalog`
- `GET /categories`
- `GET /categories/{slug}`
- `GET /products/{slug}`
- `GET /products/{slug}/variants`
- `POST /orders/track`
- `POST /checkout/tap/callback`
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/forgot-password`
- `POST /auth/reset-password`

### Authenticated
- `GET /auth/me`
- `POST /auth/logout`
- `GET /profile`
- `PUT /profile`
- `PUT /profile/password`
- `GET /addresses`
- `POST /addresses`
- `PUT /addresses/{id}`
- `DELETE /addresses/{id}`
- `GET /cart`
- `POST /cart/items`
- `PATCH /cart/items/{itemId}`
- `DELETE /cart/items/{itemId}`
- `GET /checkout/summary`
- `POST /checkout`
- `GET /checkout/orders/{order_number}/payment-status`
- `GET /orders`
- `GET /orders/{id}`

## Cart Flow
Mobile cart behavior:
- every customer has at most one active cart
- cart ownership is stored by `customer_id`
- cart does not depend on session or cookies
- cart is cleared automatically when payment is captured

Typical flow:
1. `POST /auth/login`
2. `GET /cart`
3. `POST /cart/items`
4. `PATCH /cart/items/{itemId}`
5. `GET /checkout/summary`
6. `POST /checkout`

## Checkout Request
`POST /checkout`

Required payload:

```json
{
  "first_name": "John",
  "last_name": "Customer",
  "email": "john@example.com",
  "phone": "12345678",
  "country": "Bahrain",
  "state": "Capital",
  "city": "Manama",
  "address_line_1": "Street 1",
  "address_line_2": "Building 2",
  "postal_code": "100",
  "customer_note": "Leave at the desk",
  "coupon_code": "",
  "shipping_box_type": "without_box",
  "payment_mode": "native_sdk"
}
```

`shipping_box_type` must be one of:
- `with_box`
- `without_box`

`payment_mode` currently supports:
- `native_sdk`
- `hosted_redirect`

## Tap Payment Integration
This backend is implemented as **SDK-first with hosted redirect fallback**.

`POST /checkout` returns:
- the created order
- `payment_provider: tap`
- the chosen `payment_mode`
- `tap_public_key`
- `tap_charge_id`
- `hosted_redirect_url`
- the raw hosted charge payload under `hosted_charge`

### Recommended mobile strategy
Use this order:

1. Call `POST /checkout` with `payment_mode = native_sdk`
2. Build the Tap mobile payment flow from the returned payment payload
3. After the SDK finishes, call:

```text
GET /checkout/orders/{order_number}/payment-status
```

4. Treat the order as complete only when:
   - `payment_status = paid`
   - `status = processing`

### Hosted fallback
If the SDK flow is unavailable on the client, open:

```text
payment.hosted_redirect_url
```

That keeps checkout functional without changing backend order logic.

## Flutter Notes For Tap
The backend already returns the order and payment payload needed for app-side orchestration.

Recommended app behavior:
- initialize payment only after `/checkout` succeeds
- keep `order_number` locally until payment is resolved
- if Tap SDK returns success or pending, always verify with `/checkout/orders/{order_number}/payment-status`
- if SDK integration is blocked, open `hosted_redirect_url` in an in-app browser or external browser and still verify status through the same endpoint

Because Tap SDK/package shapes can change by platform/package version, the app should treat the backend response as the source of truth for:
- `order_number`
- `tap_charge_id`
- fallback redirect URL
- final payment status

## Tap SDK vs External Link
For this backend:
- **SDK first** is supported at the contract level
- **external/hosted link** is also supported and already proven by the existing Tap hosted charge flow

Practical recommendation:
- use Tap SDK in the Flutter app when the selected package/version cleanly supports your required payment UX
- keep hosted redirect enabled as a fallback path for reliability

## Postman
The companion collection is here:

[mobile-customer-api.postman_collection.json](/d:/WORKKKK/SunFlower/Docs/mobile-customer-api.postman_collection.json)

Import it into Postman and set:
- `base_url`
- `token`
- `category_slug`
- `product_slug`
- `product_id`
- `variant_id`
- `item_id`
- `address_id`
- `order_number`

## Verification Checklist
- register or log in and save token
- fetch `/auth/me`
- browse `/home` and `/catalog`
- add an item to `/cart/items`
- fetch `/checkout/summary`
- initialize `/checkout`
- verify payment result from `/checkout/orders/{order_number}/payment-status`
- confirm the order appears under `/orders`
