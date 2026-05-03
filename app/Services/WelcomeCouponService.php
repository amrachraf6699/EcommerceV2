<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\WelcomeCoupon;
use App\Notifications\WelcomeCouponIssuedNotification;
use DomainException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class WelcomeCouponService
{
    public function issueForEmail(string $email, string $locale): WelcomeCoupon
    {
        $email = Str::lower(trim($email));

        $coupon = WelcomeCoupon::query()->where('email', $email)->first();

        if ($coupon?->isUsed()) {
            throw new DomainException(__('storefront.welcome_coupon.already_claimed', [], $locale));
        }

        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        $discount = $this->resolveDiscount();

        if (! $coupon) {
            $coupon = WelcomeCoupon::query()->create([
                'customer_id' => $customer?->id,
                'email' => $email,
                'code' => $this->generateUniqueCode(),
                'discount_type' => $discount['type'],
                'discount_value' => $discount['value'],
                'locale' => $locale,
            ]);
        } else {
            $coupon->forceFill([
                'customer_id' => $customer?->id ?? $coupon->customer_id,
                'locale' => $locale,
            ])->save();
        }

        $notification = new WelcomeCouponIssuedNotification($coupon);

        if ($customer) {
            $customer->notify($notification);
        } else {
            Notification::route('mail', $email)->notify($notification);
        }

        $coupon->forceFill(['sent_at' => now()])->save();

        return $coupon;
    }

    public function describeCurrentOffer(): array
    {
        $mode = setting('marketing.welcome_coupon_discount_mode', 'fixed_percent');
        $currency = (string) __('storefront.common.currency');
        $value = $this->normalizeDecimal(setting('marketing.welcome_coupon_value', 10));
        $min = $this->normalizeDecimal(setting('marketing.welcome_coupon_min_value', 10));
        $max = $this->normalizeDecimal(setting('marketing.welcome_coupon_max_value', 20));

        return match ($mode) {
            'fixed_amount' => [
                'headline' => $this->formatAmount($value, $currency),
            ],
            'random_percent' => [
                'headline' => $this->formatPercent($min) . ' - ' . $this->formatPercent(max($min, $max)),
            ],
            'random_amount' => [
                'headline' => $this->formatAmount($min, $currency) . ' - ' . $this->formatAmount(max($min, $max), $currency),
            ],
            default => [
                'headline' => $this->formatPercent($value),
            ],
        };
    }

    public function findRedeemableForCustomer(Customer $customer, string $code): ?WelcomeCoupon
    {
        $coupon = WelcomeCoupon::query()
            ->where('code', Str::upper(trim($code)))
            ->whereNull('used_at')
            ->first();

        if (! $coupon || ! $coupon->isOwnedBy($customer)) {
            return null;
        }

        if ($coupon->customer_id === null) {
            $coupon->forceFill(['customer_id' => $customer->id])->save();
        }

        return $coupon;
    }

    public function findRedeemableForCheckout(?Customer $customer, ?string $email, string $code): ?WelcomeCoupon
    {
        $code = Str::upper(trim($code));
        $email = Str::lower(trim((string) $email));

        if ($code === '') {
            return null;
        }

        if ($customer) {
            return $this->findRedeemableForCustomer($customer, $code);
        }

        if ($email === '') {
            return null;
        }

        $coupon = WelcomeCoupon::query()
            ->where('code', $code)
            ->whereNull('used_at')
            ->first();

        if (! $coupon || strcasecmp($coupon->email, $email) !== 0) {
            return null;
        }

        $matchedCustomer = Customer::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($matchedCustomer && ! $coupon->isOwnedBy($matchedCustomer)) {
            return null;
        }

        if ($matchedCustomer && $coupon->customer_id === null) {
            $coupon->forceFill(['customer_id' => $matchedCustomer->id])->save();
        }

        return $coupon;
    }

    public function discountAmountForSubtotal(WelcomeCoupon $coupon, float $subtotal): float
    {
        $subtotal = max(0, round($subtotal, 2));

        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = match ($coupon->discount_type) {
            'amount' => (float) $coupon->discount_value,
            default => $subtotal * ((float) $coupon->discount_value / 100),
        };

        return round(min($subtotal, max(0, $discount)), 2);
    }

    public function markAsUsed(WelcomeCoupon $coupon, Order $order): void
    {
        $coupon->forceFill([
            'order_id' => $order->id,
            'used_at' => $coupon->used_at ?: now(),
        ])->save();
    }

    /**
     * @return array{type: string, value: float}
     */
    private function resolveDiscount(): array
    {
        $mode = setting('marketing.welcome_coupon_discount_mode', 'fixed_percent');
        $value = $this->normalizeDecimal(setting('marketing.welcome_coupon_value', 10));
        $min = $this->normalizeDecimal(setting('marketing.welcome_coupon_min_value', 10));
        $max = max($min, $this->normalizeDecimal(setting('marketing.welcome_coupon_max_value', 20)));

        return match ($mode) {
            'fixed_amount' => ['type' => 'amount', 'value' => $value],
            'random_percent' => ['type' => 'percent', 'value' => (float) random_int((int) round($min), (int) round($max))],
            'random_amount' => ['type' => 'amount', 'value' => $this->randomDecimal($min, $max)],
            default => ['type' => 'percent', 'value' => $value],
        };
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'WELCOME-' . Str::upper(Str::random(8));
        } while (WelcomeCoupon::query()->where('code', $code)->exists());

        return $code;
    }

    private function randomDecimal(float $min, float $max): float
    {
        $minCents = (int) round($min * 100);
        $maxCents = (int) round($max * 100);

        return random_int(min($minCents, $maxCents), max($minCents, $maxCents)) / 100;
    }

    private function normalizeDecimal(mixed $value): float
    {
        return max(0, (float) $value);
    }

    private function formatPercent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
    }

    private function formatAmount(float $value, string $currency): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' ' . $currency;
    }
}
