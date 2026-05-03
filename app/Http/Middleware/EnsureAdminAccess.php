<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if (! $user->is_active) {
            abort(403);
        }

        if ($user->hasAnyRole(['super-admin', 'admin']) || $user->can('dashboard.view')) {
            return $next($request);
        }

        abort(403);
    }
}
