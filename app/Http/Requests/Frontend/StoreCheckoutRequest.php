<?php

namespace App\Http\Requests\Frontend;

use App\Support\StorefrontCountryCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255', Rule::in(app(StorefrontCountryCatalog::class)->countries())],
            'phone' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'short_address' => ['exclude_unless:country,Saudi Arabia', 'required_if:country,Saudi Arabia', 'string', 'regex:/^[A-Z]{4}[0-9]{4}$/'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}
