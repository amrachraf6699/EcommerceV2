<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'allowed_countries' => collect($this->input('allowed_countries', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        /** @var Coupon|null $coupon */
        $coupon = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'discount_type' => ['required', Rule::in(['percent', 'amount'])],
            'discount_value' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'min_order_subtotal' => ['nullable', 'numeric', 'min:0'],
            'max_order_subtotal' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'allowed_countries' => ['nullable', 'array'],
            'allowed_countries.*' => ['string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('discount_type') === 'percent' && (float) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'Percentage discounts cannot exceed 100.');
            }

            $min = $this->input('min_order_subtotal');
            $max = $this->input('max_order_subtotal');

            if ($min !== null && $max !== null && (float) $min > (float) $max) {
                $validator->errors()->add('max_order_subtotal', 'Maximum subtotal must be greater than or equal to the minimum subtotal.');
            }
        });
    }
}
