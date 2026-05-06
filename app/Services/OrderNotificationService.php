<?php

namespace App\Services;

use App\Enums\OrderFulfillmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminNewOrderNotification;
use App\Notifications\CustomerOrderMilestoneNotification;
use App\Notifications\CustomerOrderPlacedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class OrderNotificationService
{
    public function notifyPlaced(Order $order, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);

        if (setting_bool('notifications.customer_order_placed_notification_enabled')) {
            $this->notifyCustomer($order, new CustomerOrderPlacedNotification($order, $locale));
        }

        if (setting_bool('notifications.admin_new_order_notification_enabled')) {
            $this->adminRecipients()->each(fn (User $admin) => $admin->notify(new AdminNewOrderNotification($order)));
        }
    }

    /**
     * @param  array<string, mixed>  $original
     */
    public function notifyCustomerMilestones(Order $order, array $original, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);

        foreach ($this->detectMilestones($order, $original) as $milestone) {
            if (! setting_bool('notifications.customer_order_' . $milestone . '_notification_enabled')) {
                continue;
            }

            $this->notifyCustomer($order, new CustomerOrderMilestoneNotification($order, $milestone, $locale));
        }
    }

    /**
     * @param  array<string, mixed>  $original
     * @return array<int, string>
     */
    private function detectMilestones(Order $order, array $original): array
    {
        $milestones = [];

        if (($original['payment_status'] ?? null) !== OrderPaymentStatus::PAID->value && $order->payment_status === OrderPaymentStatus::PAID) {
            $milestones[] = 'paid';
        }

        if (($original['fulfillment_status'] ?? null) !== OrderFulfillmentStatus::SHIPPED->value && $order->fulfillment_status === OrderFulfillmentStatus::SHIPPED) {
            $milestones[] = 'shipped';
        }

        if (($original['fulfillment_status'] ?? null) !== OrderFulfillmentStatus::DELIVERED->value && $order->fulfillment_status === OrderFulfillmentStatus::DELIVERED) {
            $milestones[] = 'delivered';
        }

        $canceledBefore = ($original['payment_status'] ?? null) === OrderPaymentStatus::CANCELED->value
            || ($original['status'] ?? null) === OrderStatus::CANCELED->value;
        $canceledNow = $order->payment_status === OrderPaymentStatus::CANCELED || $order->status === OrderStatus::CANCELED;

        if (! $canceledBefore && $canceledNow) {
            $milestones[] = 'canceled';
        }

        return $milestones;
    }

    private function notifyCustomer(Order $order, object $notification): void
    {
        if ($order->customer) {
            $order->customer->notify($notification);

            return;
        }

        if (filled($order->customer_email)) {
            Notification::route('mail', $order->customer_email)->notify($notification);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function adminRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->permission('orders.view')
            ->get();
    }

    private function resolveLocale(?string $locale): string
    {
        $locale = trim((string) $locale);

        return $locale !== '' ? $locale : config('storefront.default_locale', 'ar');
    }
}
