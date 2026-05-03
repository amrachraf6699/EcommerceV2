<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    use NormalizesTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTranslatableInput(['title', 'content']);

        if (! $this->filled('slug') && filled($this->input('title.ar'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('title.ar'))]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array:ar,en'],
            'title.ar' => ['required', 'string', 'max:255'],
            'title.en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')],
            'content' => ['nullable', 'array:ar,en'],
            'content.ar' => ['nullable', 'string'],
            'content.en' => ['nullable', 'string'],
        ];
    }
}
