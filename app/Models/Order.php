<?php

namespace App\Models;

use App\Enums\OrderFulfillmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'session_id',
        'customer_id',
        'coupon_id',
        'coupon_code',
        'coupon_type',
        'coupon_value',
        'status',
        'payment_status',
        'payment_provider',
        'payment_reference',
        'payment_transaction_id',
        'payment_redirect_url',
        'fulfillment_status',
        'currency',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_postal_code',
        'shipping_same_as_billing',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_postal_code',
        'shipping_with_box',
        'customer_note',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'shipping_zone',
        'shipping_rate_source',
        'shipping_unit_cost',
        'shipping_quantity_multiplier',
        'grand_total',
        'placed_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => OrderPaymentStatus::class,
        'fulfillment_status' => OrderFulfillmentStatus::class,
        'shipping_same_as_billing' => 'boolean',
        'shipping_with_box' => 'boolean',
        'subtotal' => 'decimal:2',
        'coupon_value' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'shipping_unit_cost' => 'decimal:2',
        'shipping_quantity_multiplier' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'placed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function welcomeCoupon(): HasOne
    {
        return $this->hasOne(WelcomeCoupon::class);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return OrderStatus::options();
    }

    /**
     * @return array<string, string>
     */
    public static function paymentStatusOptions(): array
    {
        return OrderPaymentStatus::options();
    }

    /**
     * @return array<string, string>
     */
    public static function fulfillmentStatusOptions(): array
    {
        return OrderFulfillmentStatus::options();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->payment_status?->label() ?? '';
    }

    public function getFulfillmentStatusLabelAttribute(): string
    {
        return $this->fulfillment_status?->label() ?? '';
    }
}
