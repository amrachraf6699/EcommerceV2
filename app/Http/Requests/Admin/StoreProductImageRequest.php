<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    /** @var Product|null $product */
                    $product = $this->route('product');

                    if (! $value || ! $product) {
                        return;
                    }

                    if (! $product->variants()->whereKey($value)->exists()) {
                        $fail('النسخة المختارة لا تتبع هذا المنتج.');
                    }
                },
            ],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
