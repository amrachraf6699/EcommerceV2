<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SettingsManager
{
    private ?Collection $settings = null;

    public function forget(): void
    {
        $this->settings = null;
    }

    public function get(string $key, mixed $default = null, ?string $group = null): mixed
    {
        [$resolvedGroup, $resolvedKey] = $this->resolveGroupAndKey($key, $group);

        $setting = $this->settings()
            ->first(fn (Setting $setting) => $setting->group === $resolvedGroup && $setting->key === $resolvedKey);

        if ($setting) {
            return $setting->value;
        }

        return $default;
    }

    public function group(string $group): Collection
    {
        return $this->settings()
            ->where('group', $group)
            ->sortBy('sort_order')
            ->values();
    }

    public function bool(string $key, bool $default = false, ?string $group = null): bool
    {
        $value = $this->get($key, $default, $group);

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $normalized = Str::lower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function settings(): Collection
    {
        return $this->settings ??= Setting::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function resolveGroupAndKey(string $key, ?string $group = null): array
    {
        if ($group !== null) {
            return [$group, $key];
        }

        if (str_contains($key, '.')) {
            [$resolvedGroup, $resolvedKey] = explode('.', $key, 2);

            return [$resolvedGroup, $resolvedKey];
        }

        return [null, $key];
    }
}
