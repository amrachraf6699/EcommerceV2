<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user();

        if ($customer && isset($customer->is_active) && ! $customer->is_active) {
            if (method_exists($customer, 'currentAccessToken') && $customer->currentAccessToken()) {
                $customer->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => __('storefront.auth.inactive_account'),
            ], 403);
        }

        return $next($request);
    }
}
