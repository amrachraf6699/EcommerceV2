<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Str;

class CouponService
{
    public function findRedeemableForCheckout(
        ?Customer $customer,
        ?string $email,
        string $code,
        float $subtotal,
        ?string $country,
    ): ?Coupon {
        $normalizedCode = Str::upper(trim($code));

        if ($normalizedCode === '') {
            return null;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalizedCode])
            ->first();

        if (! $coupon) {
            return null;
        }

        return $this->isRedeemableForCheckout($coupon, $customer, $email, $subtotal, $country)
            ? $coupon
            : null;
    }

    public function discountAmountForSubtotal(Coupon $coupon, float $subtotal): float
    {
        $discount = match ($coupon->discount_type) {
            'amount' => (float) $coupon->discount_value,
            default => $subtotal * ((float) $coupon->discount_value / 100),
        };

        return round(min($subtotal, max(0, $discount)), 2);
    }

    public function markAsUsed(Coupon $coupon, Order $order): void
    {
        $email = trim((string) $order->customer_email);

        $coupon->redemptions()->firstOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'customer_id' => $order->customer_id,
                'customer_email' => $email,
                'used_at' => $order->placed_at ?: now(),
            ],
        );
    }

    public function statusKey(Coupon $coupon): string
    {
        if (! $coupon->is_active) {
            return 'inactive';
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function allowedCountriesSummary(Coupon $coupon): string
    {
        $countries = collect($coupon->allowed_countries)
            ->filter(fn ($country) => filled($country))
            ->values();

        if ($countries->isEmpty()) {
            return 'كل الدول';
        }

        if ($countries->count() <= 2) {
            return $countries->implode(', ');
        }

        return $countries->take(2)->implode(', ') . ' +' . ($countries->count() - 2);
    }

    private function isRedeemableForCheckout(
        Coupon $coupon,
        ?Customer $customer,
        ?string $email,
        float $subtotal,
        ?string $country,
    ): bool {
        if (! $coupon->is_active) {
            return false;
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return false;
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            return false;
        }

        if ($coupon->min_order_subtotal !== null && $subtotal < (float) $coupon->min_order_subtotal) {
            return false;
        }

        if ($coupon->max_order_subtotal !== null && $subtotal > (float) $coupon->max_order_subtotal) {
            return false;
        }

        if (! $this->countryIsAllowed($coupon, $country)) {
            return false;
        }

        if ($coupon->usage_limit !== null && $coupon->redemptions()->count() >= (int) $coupon->usage_limit) {
            return false;
        }

        if ($coupon->usage_limit_per_customer !== null) {
            if ($this->usageCountForIdentity($coupon, $customer, $email) >= (int) $coupon->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    private function countryIsAllowed(Coupon $coupon, ?string $country): bool
    {
        $allowedCountries = collect($coupon->allowed_countries)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => Str::lower(trim((string) $value)));

        if ($allowedCountries->isEmpty()) {
            return true;
        }

        $country = Str::lower(trim((string) $country));

        if ($country === '') {
            return false;
        }

        return $allowedCountries->contains($country);
    }

    private function usageCountForIdentity(Coupon $coupon, ?Customer $customer, ?string $email): int
    {
        $query = $coupon->redemptions();

        if ($customer) {
            return (int) $query
                ->where(function ($builder) use ($customer, $email): void {
                    $builder->where('customer_id', $customer->id);

                    $email = Str::lower(trim((string) $email));

                    if ($email !== '') {
                        $builder->orWhereRaw('LOWER(customer_email) = ?', [$email]);
                    }
                })
                ->count();
        }

        $email = Str::lower(trim((string) $email));

        if ($email === '') {
            return 0;
        }

        return (int) $query
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->count();
    }
}
