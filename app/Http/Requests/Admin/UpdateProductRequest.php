<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'array:ar,en'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
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
        ];
    }
}
