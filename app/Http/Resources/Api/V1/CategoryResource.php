<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url ?: ($this->image ? asset('storage/'.$this->image) : null),
            'size_guide' => $this->size_guide,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
