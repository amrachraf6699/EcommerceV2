<?php

namespace App\Enums;

enum OrderPaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'غير مدفوع',
            self::PENDING => 'بانتظار التأكيد',
            self::PAID => 'مدفوع',
            self::FAILED => 'فشل الدفع',
            self::CANCELED => 'ملغي',
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
