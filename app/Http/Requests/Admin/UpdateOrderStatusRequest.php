<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'max:255'],
            'payment_status' => ['required', 'string', 'max:255'],
            'fulfillment_status' => ['required', 'string', 'max:255'],
        ];
    }
}
