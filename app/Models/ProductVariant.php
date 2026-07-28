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

    public static function normalizeHexColor(?string $color): ?string
    {
        $color = trim((string) $color);

        if (preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color, $matches) !== 1) {
            return null;
        }

        $hex = strtoupper($matches[1]);

        if (strlen($hex) === 3) {
            $hex = implode('', array_map(static fn (string $part): string => $part . $part, str_split($hex)));
        }

        return '#' . $hex;
    }

    public function getColorHexAttribute(): ?string
    {
        return self::normalizeHexColor($this->color);
    }

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
