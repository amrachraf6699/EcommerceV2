<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UpdateCustomerProfileRequest;
use App\Http\Resources\Api\V1\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'customer' => new CustomerResource(request()->user()),
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): JsonResponse
    {
        $customer = $request->user();
        $customer->update($request->validated());

        return response()->json([
            'message' => __('storefront.auth.profile_updated'),
            'customer' => new CustomerResource($customer->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json([
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'current_password' => ['The provided password is incorrect.'],
                ],
            ], 422);
        }

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => __('storefront.auth.password_updated'),
        ]);
    }
}
