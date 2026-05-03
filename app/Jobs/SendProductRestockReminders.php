<?php

namespace App\Jobs;

use App\Models\ProductReminder;
use App\Models\ProductVariant;
use App\Notifications\ProductVariantRestockedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendProductRestockReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $variantId)
    {
    }

    public function handle(): void
    {
        $variant = ProductVariant::query()
            ->with(['product', 'reminders.customer'])
            ->find($this->variantId);

        if (! $variant || (int) $variant->stock_quantity <= 0) {
            return;
        }

        foreach ($variant->reminders->whereNull('notified_at') as $reminder) {
            /** @var ProductReminder $reminder */
            $notification = new ProductVariantRestockedNotification($variant, $reminder->locale);

            if ($reminder->customer) {
                $reminder->customer->notify($notification);
            } elseif ($reminder->email) {
                Notification::route('mail', $reminder->email)->notify($notification);
            } else {
                continue;
            }

            $reminder->forceFill([
                'active_key' => null,
                'notified_at' => now(),
            ])->save();
        }
    }
}
