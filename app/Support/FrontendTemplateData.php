<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Http\Request;

class FrontendTemplateData
{
    /**
     * @return array<string, mixed>
     */
    public static function shared(string $sessionId, ?Request $request = null): array
    {
        $request ??= request();
        $cart = Cart::query()->where('session_id', $sessionId)->first();
        $socialGroup = setting_group('social');

        return [
            'frontendBrand' => [
                'name' => (string) setting('brand.name', config('app.name')),
                'logo_path' => setting('brand.logo'),
                'logo_url' => setting('brand.logo') ? asset('storage/' . setting('brand.logo')) : null,
                'header_text_ar' => setting('brand.header_text_ar'),
                'header_text_en' => setting('brand.header_text_en'),
                'default_theme' => setting('brand.default_theme', 'dark'),
                'email' => setting('brand.email'),
                'phone' => setting('brand.phone'),
                'whatsapp_phone' => setting('brand.whatsapp_phone'),
                'address_ar' => setting('brand.address_ar'),
                'address_en' => setting('brand.address_en'),
                'address' => setting('brand.address_ar') ?: setting('brand.address_en'),
                'working_hours' => setting('brand.working_hours'),
                'country' => setting('brand.country'),
                'cr_number' => setting('brand.cr_number'),
            ],
            'frontendSocialLinks' => $socialGroup
                ->filter(fn ($setting) => filled($setting->value))
                ->mapWithKeys(fn ($setting) => [$setting->key => $setting->value]),
            'frontendNavCategories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByRaw(LocalizedQuery::expression('name'))
                ->limit(8)
                ->get(),
            'frontendCartSummary' => self::cartSummary($cart),
            'frontendLocales' => storefront_locales(),
            'frontendCurrentLocale' => storefront_locale(),
            'frontendCurrentDirection' => storefront_direction(),
            'frontendPricingContext' => self::pricingContext($request),
            'frontendTrackOrderEnabled' => self::trackOrderEnabled(),
            'frontendChatbotEnabled' => self::chatbotEnabled(),
        ];
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public static function cartSummary(?Cart $cart): array
    {
        return [
            'items_count' => (int) ($cart?->item_count ?? 0),
            'subtotal' => (float) ($cart?->subtotal ?? 0),
            'currency' => $cart?->currency ?? 'BHD',
        ];
    }

    /**
     * @return array{
     *     base_currency:string,
     *     detected_country_code:?string,
     *     detected_currency:?string,
     *     rate:?float,
     *     rate_date:?string,
     *     enabled:bool
     * }
     */
    public static function pricingContext(Request $request): array
    {
        return app(FrontendPricingContextResolver::class)->resolve($request);
    }

    public static function trackOrderEnabled(): bool
    {
        return setting_bool('marketing.track_order_enabled')
            || setting_bool('brand.track_order_enabled')
            || setting_bool('track_order_enabled');
    }

    public static function chatbotEnabled(): bool
    {
        return setting_bool('marketing.chatbot_enabled');
    }
}
