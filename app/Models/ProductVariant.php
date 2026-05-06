<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'price',
        'compare_at_price',
        'stock_quantity',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        $size = trim((string) $this->size);
        $color = trim((string) $this->color);

        if ($size !== '' && $color !== '') {
            return sprintf('(%s - %s)', $size, $color);
        }

        if ($size !== '') {
            return $size;
        }

        if ($color !== '') {
            return $color;
        }

        return 'Variant #' . $this->id;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(ProductReminder::class);
    }
}
