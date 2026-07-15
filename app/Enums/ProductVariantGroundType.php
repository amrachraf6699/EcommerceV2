<?php

namespace App\Enums;

enum ProductVariantGroundType: string
{
    case METAL = 'metal';
    case INDOOR = 'indoor';
    case REGULAR = 'regular';
    case TURF = 'turf';

    public function label(): string
    {
        return __('storefront.ground_types.' . $this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
