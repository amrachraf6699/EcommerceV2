<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait BuildsBrandedMailMessage
{
    protected function brandedMailMessage(string $subject, string $view, array $data = []): MailMessage
    {
        return (new MailMessage())
            ->subject($subject)
            ->view($view, array_merge($this->brandMailData(), $data));
    }

    /**
     * @return array<string, mixed>
     */
    protected function brandMailData(): array
    {
        $locale = $this->mailLocale();
        $logoPath = setting('brand.logo');

        return [
            'brandName' => (string) setting('brand.name', config('app.name')),
            'brandLogoUrl' => $logoPath ? asset('storage/' . $logoPath) : null,
            'brandHomeUrl' => route('storefront.home', [
                'locale' => $locale ?: config('storefront.default_locale', config('app.locale', 'ar')),
            ]),
            'mailLocale' => $locale,
            'mailDirection' => storefront_direction($locale),
        ];
    }

    protected function mailLocale(): string
    {
        return config('storefront.default_locale', config('app.locale', 'ar'));
    }
}
