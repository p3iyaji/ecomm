<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->isShopAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Shop admin access required.');
            }

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Shop admin access is limited to addresses listed in SHOP_ADMIN_EMAILS. Add your email there (comma-separated for multiple), run php artisan config:clear if needed, then try again.',
                );
        }

        return $next($request);
    }
}
