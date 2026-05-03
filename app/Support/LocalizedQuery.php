<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LocalizedQuery
{
    public static function expression(string $column, ?string $locale = null, bool $withFallback = true): string
    {
        $locale ??= app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', config('storefront.fallback_locale', 'en'));
        $localeExpression = static::jsonValueExpression($column, $locale);

        if (! $withFallback || $fallbackLocale === $locale) {
            return $localeExpression;
        }

        $fallbackExpression = static::jsonValueExpression($column, $fallbackLocale);

        return "COALESCE({$localeExpression}, {$fallbackExpression})";
    }

    protected static function jsonValueExpression(string $column, string $locale): string
    {
        $driver = DB::connection()->getDriverName();
        $jsonPath = '$."' . $locale . '"';

        if ($driver === 'sqlite') {
            return "NULLIF(json_extract({$column}, '{$jsonPath}'), '')";
        }

        return "NULLIF(JSON_UNQUOTE(JSON_EXTRACT({$column}, '{$jsonPath}')), '')";
    }

    public static function orderBy(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        return $query->orderByRaw(static::expression($column) . ' ' . $direction);
    }

    public static function whereLike(Builder $query, string $column, string $search, string $boolean = 'and'): Builder
    {
        if ($boolean === 'or') {
            return $query->orWhereRaw(static::expression($column) . ' LIKE ?', ['%' . $search . '%']);
        }

        return $query->whereRaw(static::expression($column) . ' LIKE ?', ['%' . $search . '%']);
    }
}
