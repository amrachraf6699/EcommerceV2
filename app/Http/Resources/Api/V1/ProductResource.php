<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'notes' => $this->notes,
            'is_featured' => (bool) $this->is_featured,
            'display_price' => $this->display_price !== null ? (float) $this->display_price : null,
            'display_compare_price' => $this->display_compare_price !== null ? (float) $this->display_compare_price : null,
            'display_stock_quantity' => (int) ($this->display_stock_quantity ?? 0),
            'display_label' => $this->display_label,
            'display_badge' => $this->display_badge,
            'primary_image_url' => $this->primary_image_url,
            'categories' => $this->whenLoaded('categories', fn () => CategoryResource::collection($this->categories)),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => asset('storage/'.$image->path),
                'alt_text' => $image->alt_text,
                'is_primary' => (bool) $image->is_primary,
                'sort_order' => (int) $image->sort_order,
            ])->values()),
            'default_variant' => $this->when(isset($this->default_variant), fn () => new ProductVariantResource($this->default_variant)),
            'variants' => $this->whenLoaded('variants', fn () => ProductVariantResource::collection($this->variants)),
        ];
    }
}
