<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class AuthenticateCustomer extends Middleware
{
    protected function authenticate($request, array $guards)
    {
        parent::authenticate($request, ['customer']);
    }

    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return route('storefront.auth.login', [
            'locale' => $request->route('locale', config('storefront.default_locale', config('app.locale', 'ar'))),
        ]);
    }
}
