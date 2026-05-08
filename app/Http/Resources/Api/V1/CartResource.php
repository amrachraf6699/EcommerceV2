<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'item_count' => (int) $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'currency' => $this->currency,
            'last_activity_at' => optional($this->last_activity_at)?->toAtomString(),
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'sku' => $item->sku,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
                'product' => $item->relationLoaded('product') && $item->product
                    ? new ProductResource($item->product)
                    : null,
                'variant' => $item->relationLoaded('variant') && $item->variant
                    ? new ProductVariantResource($item->variant)
                    : null,
            ])->values(),
            'summary' => [
                'items_count' => (int) $this->item_count,
                'subtotal' => (float) $this->subtotal,
                'currency' => $this->currency,
            ],
        ];
    }
}
