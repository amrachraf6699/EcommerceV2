<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductVariantGroundType;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizedColor = ProductVariant::normalizeHexColor($this->input('color'));

        if ($normalizedColor !== null) {
            $this->merge(['color' => $normalizedColor]);
        }
    }

    public function rules(): array
    {
        return [
            'size' => ['required', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[A-F0-9]{6}$/'],
            'ground_type' => ['nullable', Rule::enum(ProductVariantGroundType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
