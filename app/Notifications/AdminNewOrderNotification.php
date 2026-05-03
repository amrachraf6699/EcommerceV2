<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminNewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب جديد',
            'body' => sprintf(
                'تم إنشاء الطلب %s للعميل %s (%s) بإجمالي %s %s.',
                $this->order->order_number,
                trim($this->order->customer_first_name . ' ' . $this->order->customer_last_name),
                $this->order->customer_email,
                number_format((float) $this->order->grand_total, 2),
                $this->order->currency
            ),
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
