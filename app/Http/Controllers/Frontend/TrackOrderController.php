<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\FrontendTemplateData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function show(): View
    {
        abort_unless(FrontendTemplateData::trackOrderEnabled(), 404);

        return view('frontend.orders.track');
    }

    public function store(Request $request): View|RedirectResponse
    {
        abort_unless(FrontendTemplateData::trackOrderEnabled(), 404);

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $order = Order::query()
            ->with('items.product', 'items.variant')
            ->where('order_number', $validated['order_number'])
            ->whereRaw('LOWER(customer_email) = ?', [mb_strtolower($validated['email'])])
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->withErrors(['order_number' => __('storefront.track_order.not_found')]);
        }

        return view('frontend.orders.track', [
            'order' => $order,
        ]);
    }
}
