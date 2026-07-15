<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\BuildsBrandedMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderMilestoneNotification extends Notification implements ShouldQueue
{
    use BuildsBrandedMailMessage;
    use Queueable;

    public function __construct(
        public Order $order,
        public string $milestone,
        public string $notificationLocale,
    ) {
        $this->onQueue('mail')->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $key = 'storefront.notifications.customer.milestones.' . $this->milestone;
        $subject = __($key . '.subject', ['order' => $this->order->order_number], $this->notificationLocale);
        $line = __($key . '.line', ['order' => $this->order->order_number], $this->notificationLocale);

        return $this->brandedMailMessage($subject, 'emails.notifications.customer-order-milestone', [
            'subjectLine' => $subject,
            'eyebrow' => (string) setting('brand.name', config('app.name')),
            'title' => __($key . '.greeting', [], $this->notificationLocale),
            'intro' => $line,
            'summaryLine' => $line,
            'totalLine' => __('storefront.notifications.customer.common.total', ['total' => number_format((float) $this->order->grand_total, 2) . ' ' . $this->order->currency], $this->notificationLocale),
            'statusesLine' => __('storefront.notifications.customer.common.statuses', [
                'status' => $this->order->status_label,
                'payment' => $this->order->payment_status_label,
                'fulfillment' => $this->order->fulfillment_status_label,
            ], $this->notificationLocale),
            'actionLabel' => __('storefront.notifications.customer.milestone_action', [], $this->notificationLocale),
            'actionUrl' => $this->orderUrl(),
            'footer' => __('storefront.notifications.customer.milestone_footer', [], $this->notificationLocale),
        ]);
    }

    protected function mailLocale(): string
    {
        return $this->notificationLocale;
    }

    private function orderUrl(): string
    {
        if ($this->order->customer_id) {
            return route('storefront.orders.show', [
                'locale' => $this->notificationLocale,
                'order' => $this->order->order_number,
            ]);
        }

        return route('storefront.track-order.show', [
            'locale' => $this->notificationLocale,
        ]);
    }
}
