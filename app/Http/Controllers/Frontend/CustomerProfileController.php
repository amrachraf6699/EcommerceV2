<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UpdateCustomerPasswordRequest;
use App\Http\Requests\Frontend\UpdateCustomerProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerProfileController extends Controller
{
    public function edit(): View
    {
        $customer = auth('customer')->user();

        return view('frontend.account.profile', [
            'customer' => $customer,
            'recentOrders' => $customer->orders()->latest('placed_at')->latest()->limit(5)->get(),
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        auth('customer')->user()->update($request->validated());

        return back()->with('success', __('storefront.auth.profile_updated'));
    }

    public function updatePassword(UpdateCustomerPasswordRequest $request): RedirectResponse
    {
        auth('customer')->user()->update([
            'password' => $request->string('password')->toString(),
        ]);

        return back()->with('success', __('storefront.auth.password_updated'));
    }
}
