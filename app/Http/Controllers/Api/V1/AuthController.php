<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerForgotPasswordRequest;
use App\Http\Requests\Frontend\CustomerLoginRequest;
use App\Http\Requests\Frontend\CustomerRegisterRequest;
use App\Http\Requests\Frontend\CustomerResetPasswordRequest;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        $customer = Customer::query()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => __('storefront.auth.register_success'),
            'token' => $token,
            'token_type' => 'Bearer',
            'customer' => new CustomerResource($customer),
        ], 201);
    }

    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $customer = Customer::query()->where('email', $credentials['email'])->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return response()->json([
                'message' => __('storefront.auth.invalid_credentials'),
            ], 422);
        }

        if (! $customer->is_active) {
            return response()->json([
                'message' => __('storefront.auth.inactive_account'),
            ], 403);
        }

        $customer->tokens()->delete();
        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => __('storefront.auth.login_success'),
            'token' => $token,
            'token_type' => 'Bearer',
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'customer' => new CustomerResource(request()->user()),
        ]);
    }

    public function logout(): JsonResponse
    {
        $customer = request()->user();
        $customer?->currentAccessToken()?->delete();

        return response()->json([
            'message' => __('storefront.auth.logout_success'),
        ]);
    }

    public function forgotPassword(CustomerForgotPasswordRequest $request): JsonResponse
    {
        Password::broker('customers')->sendResetLink($request->only('email'));

        return response()->json([
            'message' => __('storefront.auth.reset_submitted'),
        ]);
    }

    public function resetPassword(CustomerResetPasswordRequest $request): JsonResponse
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

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
