<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class SettingController extends Controller
{
    /**
     * Only storefront-safe groups are exposed publicly.
     *
     * @var array<int, string>
     */
    private const EXPOSED_GROUPS = [
        'brand',
        'social',
        'analytics',
        'appearance',
        'marketing',
        'shipping',
    ];

    public function show(string $group): JsonResponse
    {
        abort_unless(in_array($group, self::EXPOSED_GROUPS, true), 404);

        $settings = setting_group($group)
            ->reject(fn ($setting) => $setting->input_type === 'password')
            ->values();

        abort_if($settings->isEmpty(), 404);

        return response()->json([
            'group' => $group,
            'settings' => $settings->map(fn ($setting) => $this->transformSetting($setting))->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformSetting($setting): array
    {
        $value = $this->normalizeValue($setting->value, (string) $setting->input_type);

        return [
            'key' => $setting->key,
            'value' => $value,
            'options' => $setting->options,
            'asset_url' => $setting->input_type === 'file' && filled($setting->value)
                ? asset('storage/'.$setting->value)
                : null,
        ];
    }

    private function normalizeValue(mixed $value, string $inputType): mixed
    {
        if ($inputType === 'boolean') {
            return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
        }

        return $value;
    }
}
