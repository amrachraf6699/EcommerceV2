<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status?->value ?? $this->status,
            'payment_status' => $this->payment_status?->value ?? $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status?->value ?? $this->fulfillment_status,
            'currency' => $this->currency,
            'customer' => [
                'first_name' => $this->customer_first_name,
                'last_name' => $this->customer_last_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],
            'shipping_address' => [
                'country' => $this->shipping_country,
                'state' => $this->shipping_state,
                'city' => $this->shipping_city,
                'address_line_1' => $this->shipping_address_line_1,
                'address_line_2' => $this->shipping_address_line_2,
                'postal_code' => $this->shipping_postal_code,
            ],
            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'tax_total' => (float) $this->tax_total,
            'shipping_total' => (float) $this->shipping_total,
            'grand_total' => (float) $this->grand_total,
            'coupon_code' => $this->coupon_code,
            'coupon_type' => $this->coupon_type,
            'payment_provider' => $this->payment_provider,
            'payment_reference' => $this->payment_reference,
            'payment_transaction_id' => $this->payment_transaction_id,
            'payment_redirect_url' => $this->payment_redirect_url,
            'placed_at' => optional($this->placed_at)?->toAtomString(),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'items' => $this->whenLoaded('items', fn () => OrderItemResource::collection($this->items)),
        ];
    }
}
