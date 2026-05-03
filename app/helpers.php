<?php

use App\Support\SettingsManager;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null, ?string $group = null): mixed
    {
        return app(SettingsManager::class)->get($key, $default, $group);
    }
}

if (! function_exists('setting_group')) {
    function setting_group(string $group): Collection
    {
        return app(SettingsManager::class)->group($group);
    }
}

if (! function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false, ?string $group = null): bool
    {
        return app(SettingsManager::class)->bool($key, $default, $group);
    }
}

if (! function_exists('storefront_locales')) {
    /**
     * @return array<string, array<string, string>>
     */
    function storefront_locales(): array
    {
        return (array) config('storefront.locales', []);
    }
}

if (! function_exists('storefront_locale')) {
    function storefront_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('storefront_direction')) {
    function storefront_direction(?string $locale = null): string
    {
        $locale ??= storefront_locale();

        return storefront_locales()[$locale]['direction'] ?? 'ltr';
    }
}

if (! function_exists('storefront_locale_name')) {
    function storefront_locale_name(string $locale): string
    {
        return storefront_locales()[$locale]['name'] ?? strtoupper($locale);
    }
}

if (! function_exists('storefront_switch_url')) {
    function storefront_switch_url(string $locale, ?Request $request = null): string
    {
        $request ??= request();
        $segments = $request->segments();
        $supportedLocales = array_keys(storefront_locales());

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('storefront.default_locale', config('app.locale', 'ar'));
        }

        if (! empty($segments) && in_array($segments[0], $supportedLocales, true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        $path = '/' . ltrim(implode('/', $segments), '/');
        $queryString = $request->getQueryString();

        return url($path) . ($queryString ? '?' . $queryString : '');
    }
}

if (! function_exists('storefront_format_money')) {
    function storefront_format_money(float|int|string|null $amount, string $currency = 'BHD'): string
    {
        return number_format((float) $amount, 2) . ' ' . strtoupper($currency);
    }
}
