<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class CustomerOrderController extends Controller
{
    public function index(): View
    {
        return view('frontend.account.orders.index', [
            'orders' => auth('customer')->user()
                ->orders()
                ->latest('placed_at')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(string $locale, Order $order): View
    {
        abort_unless($order->customer_id === auth('customer')->id(), 404);

        return view('frontend.account.orders.show', [
            'order' => $order->load('items.product', 'items.variant'),
        ]);
    }
}
