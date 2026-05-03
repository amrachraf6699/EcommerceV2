<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\WelcomeCouponService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WelcomeCouponController extends Controller
{
    public function store(Request $request, string $locale, WelcomeCouponService $service): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $service->issueForEmail($validated['email'], $locale);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => __('storefront.welcome_coupon.sent_message', [], $locale),
        ]);
    }
}
