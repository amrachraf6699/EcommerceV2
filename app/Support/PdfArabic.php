<?php

namespace App\Support;

class PdfArabic
{
    private static ?object $arabic = null;

    public static function text(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '' || ! preg_match('/\p{Arabic}/u', $value)) {
            return $value;
        }

        $arabic = self::arabic();

        if ($arabic && method_exists($arabic, 'utf8Glyphs')) {
            return $arabic->utf8Glyphs($value, 150, false, true);
        }

        return $value;
    }

    private static function arabic(): ?object
    {
        if (self::$arabic !== null) {
            return self::$arabic;
        }

        if (! class_exists(\ArPHP\I18N\Arabic::class)) {
            return null;
        }

        return self::$arabic = new \ArPHP\I18N\Arabic();
    }
}
