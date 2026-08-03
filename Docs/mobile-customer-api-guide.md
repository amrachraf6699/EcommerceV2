# Mobile Customer API Guide

## Summary
This API exposes the customer-facing mobile backend under `/api/v1`.

It covers:
- customer auth with Sanctum bearer tokens
- home/catalog/category/product browsing
- customer profile and address management
- customer-owned carts
- checkout summary and checkout initialization
- AFS COPYandPAY payment status reporting
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
  "coupon_code": ""
}
```

## AFS COPYandPAY Integration
This backend creates an AFS checkout and returns a hosted widget URL for an in-app browser or web view.

`POST /checkout` returns:
- the created order
- `payment_provider: afs`
- `payment_mode: hosted_widget`
- `checkout_id`
- `hosted_payment_url`
- `payment_widget_url`
- `payment_brands`

### Recommended mobile strategy
Use this order:

1. Call `POST /checkout`.
2. Open `payment.hosted_payment_url` in an in-app browser or web view.
3. Let the hosted page return to the storefront result page, which verifies the AFS `resourcePath` server-side.
4. After the browser returns, call:

```text
GET /checkout/orders/{order_number}/payment-status
```

5. Treat the order as complete only when:
   - `payment_status = paid`
   - `status = processing`

## Flutter Notes For AFS
The API always uses the AFS widget flow; no native SDK payload is returned.

Recommended app behavior:
- initialize payment only after `/checkout` succeeds
- keep `order_number` locally until payment is resolved
- open `hosted_payment_url` in an in-app browser or external browser
- query `/checkout/orders/{order_number}/payment-status` after the browser flow ends

The app should treat the backend response as the source of truth for:
- `order_number`
- `checkout_id`
- hosted payment URL
- final payment status

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
