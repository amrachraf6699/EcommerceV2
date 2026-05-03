<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'currency',
        'item_count',
        'subtotal',
        'last_activity_at',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
