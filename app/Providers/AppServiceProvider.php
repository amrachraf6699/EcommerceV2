<?php

namespace App\Providers;

use App\Support\FrontendTemplateData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
        $this->app->singleton(\App\Support\SettingsManager::class, fn () => new \App\Support\SettingsManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyDynamicMailConfiguration();

        URL::defaults(['locale' => config('storefront.default_locale', config('app.locale', 'ar'))]);

        View::composer('admin.partials.topbar', function ($view): void {
            $user = Auth::user();

            if (! $user) {
                $view->with([
                    'adminNotifications' => collect(),
                    'unreadAdminNotificationsCount' => 0,
                ]);

                return;
            }

            $view->with([
                'adminNotifications' => $user->notifications()->latest()->limit(8)->get(),
                'unreadAdminNotificationsCount' => $user->unreadNotifications()->count(),
            ]);
        });

        View::composer(['frontend.layouts.*', 'frontend.*'], function ($view): void {
            $view->with(FrontendTemplateData::shared(request()->session()->getId(), request()));
        });
    }

    private function applyDynamicMailConfiguration(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $host = trim((string) setting('mail.mail_host', ''));
        $port = trim((string) setting('mail.mail_port', ''));
        $username = trim((string) setting('mail.mail_username', ''));
        $password = (string) setting('mail.mail_password', '');
        $encryption = trim((string) setting('mail.mail_encryption', ''));
        $fromName = trim((string) setting('mail.mail_from_name', ''));
        $fromAddress = trim((string) setting('mail.mail_from_address', ''));

        if ($host !== '') {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
        }

        if ($port !== '') {
            Config::set('mail.mailers.smtp.port', (int) $port);
        }

        if ($username !== '') {
            Config::set('mail.mailers.smtp.username', $username);
        }

        if ($password !== '') {
            Config::set('mail.mailers.smtp.password', $password);
        }

        if ($encryption !== '') {
            Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
        }

        if ($fromAddress !== '') {
            Config::set('mail.from.address', $fromAddress);
        }

        if ($fromName !== '') {
            Config::set('mail.from.name', $fromName);
        }
    }
}
