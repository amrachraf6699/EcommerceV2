<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'display_variant_name' => $this->display_variant_name,
            'sku' => $this->sku,
            'unit_price' => (float) $this->unit_price,
            'quantity' => (int) $this->quantity,
            'line_total' => (float) $this->line_total,
        ];
    }
}
