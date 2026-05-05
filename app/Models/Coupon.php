<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'is_active',
        'starts_at',
        'ends_at',
        'min_order_subtotal',
        'max_order_subtotal',
        'usage_limit',
        'usage_limit_per_customer',
        'allowed_countries',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'min_order_subtotal' => 'decimal:2',
        'max_order_subtotal' => 'decimal:2',
        'allowed_countries' => 'array',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
