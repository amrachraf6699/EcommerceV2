<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::query()
            ->with('items')
            ->where('order_number', $validated['order_number'])
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($validated['email'])])
            ->first();

        if (! $order) {
            return response()->json([
                'message' => __('storefront.track_order.not_found'),
            ], 404);
        }

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }
}
