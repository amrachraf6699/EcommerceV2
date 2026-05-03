<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaVerifier
{
    public function siteKey(): ?string
    {
        $value = trim((string) setting('security.recaptcha_site_key', ''));

        return $value !== '' ? $value : null;
    }

    public function secretKey(): ?string
    {
        $value = trim((string) setting('security.recaptcha_secret_key', ''));

        return $value !== '' ? $value : null;
    }

    public function isConfigured(): bool
    {
        return $this->siteKey() !== null && $this->secretKey() !== null;
    }

    public function verify(?string $token, ?string $ipAddress = null): bool
    {
        if (! $this->isConfigured()) {
            return true;
        }

        $token = trim((string) $token);

        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                    'secret' => $this->secretKey(),
                    'response' => $token,
                    'remoteip' => $ipAddress,
                ], static fn ($value) => filled($value)));
        } catch (\Throwable) {
            return false;
        }

        if (! $response->ok()) {
            return false;
        }

        return (bool) $response->json('success', false);
    }
}
