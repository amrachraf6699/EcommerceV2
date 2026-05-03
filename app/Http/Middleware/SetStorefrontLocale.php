<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale', config('storefront.default_locale', config('app.locale')));
        $supportedLocales = array_keys(storefront_locales());

        if (! in_array($locale, $supportedLocales, true)) {
            abort(404);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
