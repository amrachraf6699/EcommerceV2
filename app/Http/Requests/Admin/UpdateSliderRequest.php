<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSliderRequest extends FormRequest
{
    use NormalizesTranslatableInput;

    public function authorize(): bool
    {
        return $this->user()?->can('sliders.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTranslatableInput(['title', 'subtitle']);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'array:ar,en'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'array:ar,en'],
            'subtitle.ar' => ['nullable', 'string', 'max:255'],
            'subtitle.en' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
            'link' => ['nullable', 'string', 'max:2048'],
            'text_color' => ['required', 'string', 'max:50'],
            'button_background_color' => ['required', 'string', 'max:50'],
            'button_text_color' => ['required', 'string', 'max:50'],
            'horizontal_align' => ['required', Rule::in(['left', 'center', 'right'])],
            'vertical_align' => ['required', Rule::in(['top', 'center', 'bottom'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
