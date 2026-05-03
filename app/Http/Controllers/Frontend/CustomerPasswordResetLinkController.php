<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class CustomerPasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('frontend.auth.forgot-password');
    }

    public function store(CustomerForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        return redirect()
            ->route('storefront.home')
            ->with('success', __('storefront.auth.reset_submitted'));
    }
}
