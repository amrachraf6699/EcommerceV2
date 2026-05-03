<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerLoginRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('frontend.auth.login');
    }

    public function store(CustomerLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::guard('customer')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('storefront.auth.invalid_credentials')])
                ->withInput($request->only('email'))
                ->with('auth_modal_tab', 'login');
        }

        $request->session()->regenerate();

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        if (! $customer->is_active) {
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => __('storefront.auth.inactive_account')])
                ->withInput($request->only('email'))
                ->with('auth_modal_tab', 'login');
        }

        return redirect()
            ->intended(route('storefront.profile.edit'))
            ->with('success', __('storefront.auth.login_success'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::guard('customer')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('storefront.home')
            ->with('success', __('storefront.auth.logout_success'));
    }
}
