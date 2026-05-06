<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\AdminArabic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->settingsPayload() as $index => $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, ['sort_order' => $index + 1])
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function settingsPayload(): array
    {
        $path = base_path('settings groups.txt');
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
        $group = null;
        $settings = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (Str::startsWith($line, '-')) {
                $group = Str::of($line)->after('-')->before(':')->trim()->toString();

                continue;
            }

            if ($group === null) {
                continue;
            }

            if ($group === 'mail' && Str::contains(Str::lower($line), 'smtp')) {
                foreach ($this->mailSettings() as $mailSetting) {
                    $settings[] = $mailSetting;
                }

                continue;
            }

            if ($group === 'analytics') {
                $settings[] = $this->makeAnalyticsSettingRecord($line);

                continue;
            }

            if ($group === 'shipping') {
                $settings[] = $this->makeShippingSettingRecord($line);

                continue;
            }

            if ($group === 'marketing') {
                $settings[] = $this->makeMarketingSettingRecord($line);

                continue;
            }

            if ($group === 'appearance') {
                $settings[] = $this->makeAppearanceSettingRecord($line);

                continue;
            }

            if ($group === 'security') {
                $settings[] = $this->makeSecuritySettingRecord($line);

                continue;
            }

            if ($group === 'notifications') {
                $settings[] = $this->makeNotificationSettingRecord($line);

                continue;
            }

            if ($group === 'payment') {
                $settings[] = $this->makePaymentSettingRecord($line);

                continue;
            }

            if ($group === 'brand') {
                $settings[] = $this->makeBrandSettingRecord($line);

                continue;
            }

            $settings[] = $this->makeSettingRecord($group, $line);
        }

        return $settings;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mailSettings(): array
    {
        return [
            $this->makeSettingRecord('mail', 'mail_host', 'text', 'SMTP host name'),
            $this->makeSettingRecord('mail', 'mail_port', 'number', 'SMTP port'),
            $this->makeSettingRecord('mail', 'mail_username', 'text', 'SMTP username'),
            $this->makeSettingRecord('mail', 'mail_password', 'password', 'SMTP password'),
            $this->makeSettingRecord('mail', 'mail_encryption', 'select', 'SMTP encryption', ['tls', 'ssl', 'null']),
            $this->makeSettingRecord('mail', 'mail_from_name', 'text', 'Default sender name'),
            $this->makeSettingRecord('mail', 'mail_from_address', 'email', 'Default sender email'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function makeAnalyticsSettingRecord(string $key): array
    {
        $descriptions = [
            'google_analytics_measurement_id' => AdminArabic::settingsDescription('google_analytics_measurement_id'),
            'google_tag_manager_id' => AdminArabic::settingsDescription('google_tag_manager_id'),
            'google_search_console_verification_id' => AdminArabic::settingsDescription('google_search_console_verification_id'),
            'google_ads_conversion_id' => AdminArabic::settingsDescription('google_ads_conversion_id'),
            'google_ads_conversion_label' => AdminArabic::settingsDescription('google_ads_conversion_label'),
            'facebook_pixel_id' => AdminArabic::settingsDescription('facebook_pixel_id'),
            'meta_domain_verification_id' => AdminArabic::settingsDescription('meta_domain_verification_id'),
            'tiktok_pixel_id' => AdminArabic::settingsDescription('tiktok_pixel_id'),
            'snapchat_pixel_id' => AdminArabic::settingsDescription('snapchat_pixel_id'),
            'pinterest_tag_id' => AdminArabic::settingsDescription('pinterest_tag_id'),
            'microsoft_clarity_project_id' => AdminArabic::settingsDescription('microsoft_clarity_project_id'),
            'bing_uet_tag_id' => AdminArabic::settingsDescription('bing_uet_tag_id'),
        ];

        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return $this->makeSettingRecord(
            'analytics',
            $normalizedKey,
            'text',
            $descriptions[$normalizedKey] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makeShippingSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return match ($normalizedKey) {
            'shipping_gulf_cost' => array_merge(
                $this->makeSettingRecord('shipping', $normalizedKey, 'number'),
                ['value' => '0']
            ),
            'shipping_others_cost', 'vat_value' => array_merge(
                $this->makeSettingRecord('shipping', $normalizedKey, 'number'),
                ['value' => match ($normalizedKey) {
                    'shipping_others_cost' => '15',
                    default => null,
                }]
            ),
            default => $this->makeSettingRecord('shipping', $normalizedKey),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function makeAppearanceSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        if (in_array($normalizedKey, ['home_brands_section_background_color', 'home_new_arrivals_section_background_color'], true)) {
            return array_merge(
                $this->makeSettingRecord(
                    'appearance',
                    $normalizedKey,
                    'color',
                    match ($normalizedKey) {
                        'home_brands_section_background_color' => 'Choose the background color for the home brands section.',
                        'home_new_arrivals_section_background_color' => 'Choose the background color for the home new arrivals section.',
                        default => null,
                    }
                ),
                ['value' => match ($normalizedKey) {
                    'home_brands_section_background_color' => '#000000',
                    'home_new_arrivals_section_background_color' => '#121212',
                    default => null,
                }]
            );
        }

        return $this->makeSettingRecord(
            'appearance',
            $normalizedKey,
            'select',
            match ($normalizedKey) {
                'categories_appearance' => 'Choose how category sections are presented on the storefront.',
                'products_appearance' => 'Choose how product sections are presented on the storefront.',
                'clients_appearance' => 'Choose how client sections are presented on the storefront.',
                default => null,
            },
            in_array($normalizedKey, ['products_appearance', 'clients_appearance'], true)
                ? ['Masonry Layout', 'Horizontal Scroll', 'Grid']
                : ['Masonry Layout', 'Horizontal Scroll']
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makeMarketingSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return match ($normalizedKey) {
            'welcome_coupon_discount_mode' => $this->makeSettingRecord(
                'marketing',
                $normalizedKey,
                'select',
                'Choose whether the welcome coupon is fixed or randomly generated, and whether it is a percent or a fixed amount.',
                ['fixed_percent', 'fixed_amount', 'random_percent', 'random_amount']
            ),
            'welcome_coupon_value', 'welcome_coupon_min_value', 'welcome_coupon_max_value' => $this->makeSettingRecord(
                'marketing',
                $normalizedKey,
                'number'
            ),
            default => $this->makeSettingRecord('marketing', $key),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function makeBrandSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return match ($normalizedKey) {
            'default_theme' => $this->makeSettingRecord(
                'brand',
                $normalizedKey,
                'select',
                'Choose the default storefront theme when no saved preference exists.',
                ['dark', 'light']
            ),
            'address_ar', 'address_en' => $this->makeSettingRecord('brand', $normalizedKey, 'textarea'),
            'email' => $this->makeSettingRecord('brand', $normalizedKey, 'email'),
            default => $this->makeSettingRecord('brand', $normalizedKey),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function makePaymentSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return match ($normalizedKey) {
            'tap_secret_key', 'tap_public_key', 'tap_webhook_secret' => $this->makeSettingRecord(
                'payment',
                $normalizedKey,
                'password'
            ),
            default => $this->makeSettingRecord('payment', $normalizedKey),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function makeSecuritySettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return match ($normalizedKey) {
            'recaptcha_secret_key' => $this->makeSettingRecord('security', $normalizedKey, 'password'),
            default => $this->makeSettingRecord('security', $normalizedKey),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function makeNotificationSettingRecord(string $key): array
    {
        [$normalizedKey] = $this->normalizeSettingDefinition($key);

        return array_merge(
            $this->makeSettingRecord('notifications', $normalizedKey, 'boolean'),
            ['value' => '1']
        );
    }

    /**
     * @param array<int, string>|null $options
     * @return array<string, mixed>
     */
    private function makeSettingRecord(
        string $group,
        string $key,
        ?string $inputType = null,
        ?string $description = null,
        ?array $options = null
    ): array {
        [$normalizedKey, $detectedType] = $this->normalizeSettingDefinition($key);
        $inputType ??= $detectedType;

        return [
            'group' => $group,
            'key' => $normalizedKey,
            'label' => AdminArabic::settingsLabel($normalizedKey),
            'value' => null,
            'input_type' => $inputType,
            'description' => AdminArabic::settingsDescription($normalizedKey, $description),
            'options' => $options,
            'is_public' => in_array($group, ['social', 'analytics'], true),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeSettingDefinition(string $rawDefinition): array
    {
        $definition = trim($rawDefinition);

        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $definition, $matches)) {
            $key = Str::snake(trim($matches[1]));
            $hint = Str::lower(trim($matches[2]));

            return [$key, Str::contains($hint, 'boolean') ? 'boolean' : 'text'];
        }

        $key = Str::snake($definition);

        return match ($key) {
            'logo' => [$key, 'file'],
            'address_ar', 'address_en' => [$key, 'textarea'],
            default => [$key, 'text'],
        };
    }
}
