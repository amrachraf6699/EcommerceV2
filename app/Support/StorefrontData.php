<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Collection;

class StorefrontData
{
    /**
     * @return array<string, mixed>
     */
    public static function shared(string $sessionId): array
    {
        $settings = Setting::query()
            ->where(function ($query): void {
                $query
                    ->where('is_public', true)
                    ->orWhere('group', 'brand');
            })
            ->get()
            ->keyBy('key');

        $cart = Cart::query()
            ->where('session_id', $sessionId)
            ->first();

        return [
            'storefrontSettings' => $settings,
            'storefrontBrand' => self::brand($settings),
            'storefrontSocialLinks' => self::socialLinks($settings),
            'storefrontFooterData' => self::footerData($settings),
            'storefrontNavCategories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'storefrontCartSummary' => [
                'items_count' => (int) ($cart?->item_count ?? 0),
                'subtotal' => (float) ($cart?->subtotal ?? 0),
                'currency' => $cart?->currency ?? 'USD',
            ],
        ];
    }

    /**
     * @param Collection<string, Setting> $settings
     * @return array<string, string|null>
     */
    private static function brand(Collection $settings): array
    {
        $logo = $settings->get('logo')?->value;

        return [
            'name' => $settings->get('name')?->value ?: config('app.name'),
            'logo_path' => $logo,
            'logo_url' => $logo ? asset('storage/' . $logo) : null,
            'address' => $settings->get('address')?->value,
        ];
    }

    /**
     * @param Collection<string, Setting> $settings
     * @return array<string, string>
     */
    private static function socialLinks(Collection $settings): array
    {
        return collect([
            'facebook' => $settings->get('facebook')?->value,
            'instagram' => $settings->get('instagram')?->value,
            'snapchat' => $settings->get('snapchat')?->value,
            'tiktok' => $settings->get('tiktok')?->value,
            'twitter' => $settings->get('twitter')?->value,
        ])->filter(fn ($value) => filled($value))->all();
    }

    /**
     * @param Collection<string, Setting> $settings
     * @return array<string, mixed>
     */
    private static function footerData(Collection $settings): array
    {
        return [
            'address' => $settings->get('address')?->value,
            'social_count' => count(self::socialLinks($settings)),
        ];
    }
}
