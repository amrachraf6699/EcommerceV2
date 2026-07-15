<?php

namespace App\Models;

use App\Enums\ProductVariantGroundType;
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
        'ground_type',
        'price',
        'compare_at_price',
        'stock_quantity',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'ground_type' => ProductVariantGroundType::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        $parts = collect([$this->size, $this->color, $this->ground_type?->label()])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->values();

        if ($parts->isNotEmpty()) {
            return $parts->count() > 1
                ? sprintf('(%s)', $parts->implode(' - '))
                : $parts->first();
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
