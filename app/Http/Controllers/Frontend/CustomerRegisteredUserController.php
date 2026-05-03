<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerRegisterRequest;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('frontend.auth.register');
    }

    public function store(CustomerRegisterRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        event(new Registered($customer));

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()
            ->route('storefront.profile.edit')
            ->with('success', __('storefront.auth.register_success'));
    }
}
