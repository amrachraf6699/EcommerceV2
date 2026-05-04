<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesTranslatableInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSliderRequest extends FormRequest
{
    use NormalizesTranslatableInput;

    public function authorize(): bool
    {
        return $this->user()?->can('sliders.create') ?? false;
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
            'image' => ['required', 'image', 'max:5120'],
            'link' => ['nullable', 'string', 'max:2048'],
            'text_color' => ['required', 'string', 'max:50'],
            'button_background_color' => ['required', 'string', 'max:50'],
            'button_text_color' => ['required', 'string', 'max:50'],
            'overlay_opacity_start' => ['required', 'numeric', 'min:0', 'max:1'],
            'overlay_opacity_end' => ['required', 'numeric', 'min:0', 'max:1'],
            'horizontal_align' => ['required', Rule::in(['left', 'center', 'right'])],
            'vertical_align' => ['required', Rule::in(['top', 'center', 'bottom'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
