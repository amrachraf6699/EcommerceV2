<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\WelcomeCoupon;
use App\Support\StorefrontCountryCatalog;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CheckoutPricingService
{
    public function __construct(
        private readonly StorefrontCountryCatalog $countryCatalog,
        private readonly WelcomeCouponService $welcomeCouponService,
    ) {
    }

    /**
     * @param  array{
     *     country?:mixed,
     *     detected_country_code?:mixed,
     *     customer?:mixed,
     *     email?:mixed,
     *     coupon_code?:mixed
     * }  $context
     * @return array{
     *     country:?string,
     *     shipping_zone:?string,
     *     shipping_rate_source:?string,
     *     coupon_code:?string,
     *     coupon_discount_total:float,
     *     discount_total:float,
     *     subtotal_before_discount:float,
     *     subtotal_after_discount:float,
     *     shipping_total:float,
     *     tax_total:float,
     *     subtotal:float,
     *     grand_total:float,
     *     coupon_applied:bool,
     *     coupon_error:?string,
     *     error:?string
     * }
     */
    public function calculate(?Cart $cart, array $context = []): array
    {
        $country = $this->resolveCountry(
            Arr::get($context, 'country'),
            Arr::get($context, 'detected_country_code'),
        );

        $subtotal = $this->round((float) ($cart?->subtotal ?? 0));
        $itemCount = max(0, (int) ($cart?->item_count ?? 0));
        $shippingZone = $this->countryCatalog->resolveShippingZone($country);
        $coupon = $this->resolveCoupon(
            Arr::get($context, 'customer'),
            Arr::get($context, 'email'),
            Arr::get($context, 'coupon_code'),
        );
        $couponDiscountTotal = $coupon ? $this->welcomeCouponService->discountAmountForSubtotal($coupon, $subtotal) : 0.0;
        $subtotalAfterDiscount = $this->round(max(0, $subtotal - $couponDiscountTotal));

        if ($country !== null && $country !== '' && $shippingZone === null) {
            return [
                'country' => $country,
                'shipping_zone' => null,
                'shipping_rate_source' => null,
                'coupon_code' => $coupon?->code,
                'coupon_discount_total' => $couponDiscountTotal,
                'discount_total' => $couponDiscountTotal,
                'subtotal_before_discount' => $subtotal,
                'subtotal_after_discount' => $subtotalAfterDiscount,
                'shipping_total' => 0.0,
                'tax_total' => 0.0,
                'subtotal' => $subtotal,
                'grand_total' => $subtotalAfterDiscount,
                'coupon_applied' => $coupon !== null,
                'coupon_error' => $this->couponErrorMessage(
                    Arr::get($context, 'coupon_code'),
                    Arr::get($context, 'email'),
                    $coupon,
                ),
                'error' => __('storefront.checkout_shipping_unavailable_country'),
            ];
        }

        $shippingTotal = 0.0;
        $shippingRateSource = null;

        if ($shippingZone === 'gulf') {
            $shippingRateSource = 'shipping.shipping_gulf_cost';
            $shippingTotal = $this->round((float) $this->setting($shippingRateSource, '0'));
        } elseif ($shippingZone === 'europe_america') {
            if ($itemCount >= 3) {
                $shippingRateSource = 'shipping.shipping_europe_america_3_plus_cost';
                $shippingTotal = $this->round((float) $this->setting($shippingRateSource, '0') * $itemCount);
            } elseif ($itemCount > 0) {
                $shippingRateSource = 'shipping.shipping_europe_america_1_2_cost';
                $shippingTotal = $this->round((float) $this->setting($shippingRateSource, '0') * $itemCount);
            }
        }

        $taxTotal = 0.0;

        if ($this->settingBool('shipping.enable_vat')) {
            $vatPercentage = (float) $this->setting('shipping.vat_value', '0');
            $taxableAmount = $subtotalAfterDiscount + $shippingTotal;
            $taxTotal = $this->round($taxableAmount * ($vatPercentage / 100));
        }

        return [
            'country' => $country,
            'shipping_zone' => $shippingZone,
            'shipping_rate_source' => $shippingRateSource,
            'coupon_code' => $coupon?->code,
            'coupon_discount_total' => $couponDiscountTotal,
            'discount_total' => $couponDiscountTotal,
            'subtotal_before_discount' => $subtotal,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'shipping_total' => $shippingTotal,
            'tax_total' => $taxTotal,
            'subtotal' => $subtotal,
            'grand_total' => $this->round($subtotalAfterDiscount + $shippingTotal + $taxTotal),
            'coupon_applied' => $coupon !== null,
            'coupon_error' => $this->couponErrorMessage(
                Arr::get($context, 'coupon_code'),
                Arr::get($context, 'email'),
                $coupon,
            ),
            'error' => null,
        ];
    }

    public function requireRedeemableCoupon(?Customer $customer, ?string $email, ?string $couponCode): ?WelcomeCoupon
    {
        $couponCode = trim((string) $couponCode);

        if ($couponCode === '') {
            return null;
        }

        if (trim((string) $email) === '' && ! $customer) {
            return null;
        }

        return $this->welcomeCouponService->findRedeemableForCheckout($customer, $email, $couponCode);
    }

    private function resolveCountry(mixed $country, mixed $detectedCountryCode): ?string
    {
        $country = trim((string) $country);

        if ($country !== '') {
            return $country;
        }

        return $this->countryCatalog->countryNameFromDetectedCode((string) $detectedCountryCode);
    }

    private function resolveCoupon(?Customer $customer, mixed $email, mixed $couponCode): ?WelcomeCoupon
    {
        $couponCode = trim((string) $couponCode);

        if ($couponCode === '') {
            return null;
        }

        return $this->welcomeCouponService->findRedeemableForCheckout(
            $customer,
            is_string($email) ? $email : null,
            $couponCode,
        );
    }

    private function couponErrorMessage(mixed $couponCode, mixed $email, ?WelcomeCoupon $coupon): ?string
    {
        $couponCode = trim((string) $couponCode);

        if ($couponCode === '') {
            return null;
        }

        if (trim((string) $email) === '') {
            return __('storefront.checkout_coupon_email_required');
        }

        if ($coupon === null) {
            return __('storefront.checkout_coupon_invalid');
        }

        return null;
    }

    private function round(float $amount): float
    {
        return round($amount, 2);
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        [$group, $settingKey] = explode('.', $key, 2);

        return Setting::query()
            ->where('group', $group)
            ->where('key', $settingKey)
            ->value('value') ?? $default;
    }

    private function settingBool(string $key, bool $default = false): bool
    {
        $value = $this->setting($key, $default ? '1' : '0');

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return in_array(Str::lower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}
