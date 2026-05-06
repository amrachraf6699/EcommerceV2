<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderFulfillmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'payment_status' => ['required', Rule::enum(OrderPaymentStatus::class)],
            'fulfillment_status' => ['required', Rule::enum(OrderFulfillmentStatus::class)],
        ];
    }
}
