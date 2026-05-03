<?php

namespace App\Notifications;

use App\Models\WelcomeCoupon;
use App\Notifications\Concerns\BuildsBrandedMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeCouponIssuedNotification extends Notification
{
    use BuildsBrandedMailMessage;
    use Queueable;

    public function __construct(public WelcomeCoupon $coupon)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->coupon->locale;
        $discount = $this->coupon->discount_type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->coupon->discount_value, 2, '.', ''), '0'), '.') . '%'
            : rtrim(rtrim(number_format((float) $this->coupon->discount_value, 2, '.', ''), '0'), '.') . ' ' . __('storefront.common.currency', [], $locale);
        $subject = __('storefront.welcome_coupon.email_subject', [], $locale);
        $intro = __('storefront.welcome_coupon.email_intro', ['discount' => $discount], $locale);

        return $this->brandedMailMessage($subject, 'emails.notifications.welcome-coupon-issued', [
            'subjectLine' => $subject,
            'eyebrow' => (string) setting('brand.name', config('app.name')),
            'title' => __('storefront.welcome_coupon.email_greeting', [], $locale),
            'intro' => $intro,
            'summaryLine' => $intro,
            'codeLabel' => __('storefront.welcome_coupon.email_code_label', [], $locale),
            'couponCode' => $this->coupon->code,
            'accountOnlyLine' => __('storefront.welcome_coupon.email_account_only', ['email' => $this->coupon->email], $locale),
            'actionLabel' => __('storefront.welcome_coupon.email_action', [], $locale),
            'actionUrl' => route('storefront.home', ['locale' => $locale]),
            'footer' => __('storefront.welcome_coupon.email_footer', [], $locale),
        ]);
    }

    protected function mailLocale(): string
    {
        return $this->coupon->locale;
    }
}
