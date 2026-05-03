<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomeCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'email',
        'code',
        'discount_type',
        'discount_value',
        'locale',
        'sent_at',
        'used_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'sent_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isOwnedBy(Customer $customer): bool
    {
        if ($this->customer_id !== null) {
            return $this->customer_id === $customer->id;
        }

        return strcasecmp($this->email, $customer->email) === 0;
    }
}
