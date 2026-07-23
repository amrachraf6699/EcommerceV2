<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductVariantGroundType;
use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use App\Rules\SafeSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use NormalizesTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTranslatableInput([
            'name',
            'short_description',
            'description',
            'notes',
            'meta_title',
            'meta_description',
        ]);

        if (! $this->filled('slug') && filled($this->input('name.ar'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('name.ar'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array:ar,en'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new SafeSlug(), Rule::unique('products', 'slug')],
            'label' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'array:ar,en'],
            'short_description.ar' => ['nullable', 'string'],
            'short_description.en' => ['nullable', 'string'],
            'description' => ['nullable', 'array:ar,en'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'notes' => ['nullable', 'array:ar,en'],
            'notes.ar' => ['nullable', 'string'],
            'notes.en' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'array:ar,en'],
            'meta_title.ar' => ['nullable', 'string', 'max:255'],
            'meta_title.en' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'array:ar,en'],
            'meta_description.ar' => ['nullable', 'string'],
            'meta_description.en' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.size' => ['nullable', 'required_with:variants.*.color,variants.*.ground_type,variants.*.price,variants.*.stock_quantity,variants.*.compare_at_price', 'string', 'max:255'],
            'variants.*.color' => ['nullable', 'required_with:variants.*.size,variants.*.ground_type,variants.*.price,variants.*.stock_quantity,variants.*.compare_at_price', 'string', 'max:255'],
            'variants.*.ground_type' => ['nullable', Rule::enum(ProductVariantGroundType::class)],
            'variants.*.price' => ['nullable', 'required_with:variants.*.size,variants.*.color,variants.*.ground_type,variants.*.stock_quantity', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'required_with:variants.*.size,variants.*.color,variants.*.ground_type,variants.*.price', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
            'image_alt_text' => ['nullable', 'string', 'max:255'],
            'image_sort_order' => ['nullable', 'integer', 'min:0'],
            'image_variant_index' => ['nullable', 'integer', 'min:0'],
            'images_primary' => ['nullable', 'boolean'],
        ];
    }
}
