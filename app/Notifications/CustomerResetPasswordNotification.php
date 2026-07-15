<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token, private readonly string $storefrontLocale)
    {
        parent::__construct($token);
        $this->onQueue('mail')->afterCommit();
    }

    protected function resetUrl($notifiable): string
    {
        return route('storefront.auth.password.reset', [
            'locale' => $this->storefrontLocale ?: config('storefront.default_locale', config('app.locale', 'ar')),
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = $this->storefrontLocale ?: config('storefront.default_locale', config('app.locale', 'ar'));
        $brandName = (string) setting('brand.name', config('app.name'));
        $logoPath = setting('brand.logo');

        return (new MailMessage())
            ->subject(__('storefront.auth.reset_title', [], $locale))
            ->view('emails.notifications.customer-reset-password', [
                'subjectLine' => __('storefront.auth.reset_title', [], $locale),
                'brandName' => $brandName,
                'brandLogoUrl' => $logoPath ? asset('storage/' . $logoPath) : null,
                'brandHomeUrl' => route('storefront.home', ['locale' => $locale]),
                'mailLocale' => $locale,
                'mailDirection' => storefront_direction($locale),
                'eyebrow' => $brandName,
                'title' => __('storefront.auth.reset_password', [], $locale),
                'intro' => __('storefront.auth.reset_password_copy', [], $locale),
                'summaryLine' => __('storefront.auth.reset_copy', [], $locale),
                'securityLine' => __('storefront.auth.reset_submitted', [], $locale),
                'actionLabel' => __('storefront.auth.send_reset_link', [], $locale),
                'actionUrl' => $this->resetUrl($notifiable),
                'footer' => $brandName,
            ]);
    }
}
