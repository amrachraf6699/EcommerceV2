<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SafeSlug implements Rule
{
    public static function isValid(string $slug): bool
    {
        return preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D', $slug) === 1;
    }

    public function passes($attribute, $value): bool
    {
        return is_string($value) && self::isValid($value);
    }

    public function message(): string
    {
        return 'The :attribute may contain only lowercase letters, numbers, and single hyphens between words.';
    }
}
