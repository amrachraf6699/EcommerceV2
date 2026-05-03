<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'customer_id',
        'email',
        'locale',
        'active_key',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function activeKey(int $variantId, ?int $customerId, ?string $email): string
    {
        $recipient = $customerId ? 'customer:' . $customerId : 'email:' . strtolower((string) $email);

        return $variantId . ':' . $recipient;
    }
}
