<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = Coupon::query()
            ->withCount('redemptions')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            $now = now();

            $query->where(function ($builder) use ($status, $now): void {
                if ($status === 'active') {
                    $builder
                        ->where('is_active', true)
                        ->where(fn ($nested) => $nested->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                        ->where(fn ($nested) => $nested->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
                } elseif ($status === 'scheduled') {
                    $builder->where('is_active', true)->whereNotNull('starts_at')->where('starts_at', '>', $now);
                } elseif ($status === 'expired') {
                    $builder->whereNotNull('ends_at')->where('ends_at', '<', $now);
                } elseif ($status === 'inactive') {
                    $builder->where('is_active', false);
                }
            });
        }

        return view('admin.coupons.index', [
            'coupons' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        Coupon::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'allowed_countries' => $request->validated('allowed_countries') ?: null,
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'تم إنشاء الكوبون بنجاح.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'allowed_countries' => $request->validated('allowed_countries') ?: null,
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'تم تحديث الكوبون بنجاح.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'تم حذف الكوبون.');
    }
}
