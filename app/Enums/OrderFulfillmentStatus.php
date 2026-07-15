<?php

namespace App\Enums;

enum OrderFulfillmentStatus: string
{
    case UNFULFILLED = 'unfulfilled';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::UNFULFILLED => 'قيد التجهيز',
            self::SHIPPED => 'تم الشحن',
            self::DELIVERED => 'تم التسليم',
        };
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
