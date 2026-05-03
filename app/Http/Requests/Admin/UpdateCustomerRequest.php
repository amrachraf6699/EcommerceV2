<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.update') ?? false;
    }

    public function rules(): array
    {
        /** @var Customer $customer */
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer)],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
