<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('clients.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'position' => ['nullable', 'string', 'max:255'],
        ];
    }
}
