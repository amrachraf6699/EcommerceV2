<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use App\Models\Category;
use App\Rules\SafeSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    use NormalizesTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTranslatableInput(['name', 'description']);

        if (! $this->filled('slug') && filled($this->input('name.ar'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('name.ar'))]);
        }
    }

    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'array:ar,en'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new SafeSlug(), Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'array:ar,en'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'size_guide' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
