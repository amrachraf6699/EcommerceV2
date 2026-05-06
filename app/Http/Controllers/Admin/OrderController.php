<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderNotificationService $orderNotificationService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Order::query()->latest('placed_at')->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_email', 'like', "%{$search}%")
                ->orWhere('customer_first_name', 'like', "%{$search}%")
                ->orWhere('customer_last_name', 'like', "%{$search}%"));
        }

        foreach (['status', 'payment_status', 'fulfillment_status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(12)->withQueryString(),
            'statusOptions' => Order::statusOptions(),
            'paymentStatusOptions' => Order::paymentStatusOptions(),
            'fulfillmentStatusOptions' => Order::fulfillmentStatusOptions(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.product', 'items.variant'),
            'statusOptions' => Order::statusOptions(),
            'paymentStatusOptions' => Order::paymentStatusOptions(),
            'fulfillmentStatusOptions' => Order::fulfillmentStatusOptions(),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $original = [
            'status' => $order->status?->value,
            'payment_status' => $order->payment_status?->value,
            'fulfillment_status' => $order->fulfillment_status?->value,
        ];

        $order->update($request->validated());

        $this->orderNotificationService->notifyCustomerMilestones(
            $order->fresh('customer'),
            $original,
            app()->getLocale()
        );

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }
}
