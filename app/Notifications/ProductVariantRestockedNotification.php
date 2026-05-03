<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use App\Notifications\Concerns\BuildsBrandedMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductVariantRestockedNotification extends Notification implements ShouldQueue
{
    use BuildsBrandedMailMessage;
    use Queueable;

    public function __construct(
        public ProductVariant $variant,
        public string $reminderLocale,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->variant->product->name;
        $variantName = $this->variant->display_name;
        $subject = __('storefront.product.reminder_email_subject', ['product' => $productName, 'variant' => $variantName], $this->reminderLocale);
        $line = __('storefront.product.reminder_email_line', ['product' => $productName, 'variant' => $variantName], $this->reminderLocale);

        return $this->brandedMailMessage($subject, 'emails.notifications.product-restocked', [
            'subjectLine' => $subject,
            'eyebrow' => (string) setting('brand.name', config('app.name')),
            'title' => __('storefront.product.reminder_email_greeting', [], $this->reminderLocale),
            'intro' => $line,
            'summaryLine' => $line,
            'productLine' => $productName . ' | ' . $variantName,
            'actionLabel' => __('storefront.product.reminder_email_action', [], $this->reminderLocale),
            'actionUrl' => route('storefront.products.show', [
                'locale' => $this->reminderLocale,
                'product' => $this->variant->product->slug,
            ]),
            'footer' => __('storefront.product.reminder_email_footer', [], $this->reminderLocale),
        ]);
    }

    protected function mailLocale(): string
    {
        return $this->reminderLocale;
    }
}
