<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerResetPasswordRequest;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CustomerNewPasswordController extends Controller
{
    public function create(Request $request, string $locale, string $token): View
    {
        return view('frontend.auth.reset-password', [
            'request' => $request,
            'token' => $token,
        ]);
    }

    public function store(CustomerResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('customers')->reset(
            $request->validated(),
            function (Customer $customer) use ($request): void {
                $customer->forceFill([
                    'password' => Hash::make($request->string('password')->toString()),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($customer));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('storefront.auth.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
