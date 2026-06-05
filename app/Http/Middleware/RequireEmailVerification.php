<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireEmailVerification
{
    protected array $except = [
        'logout',
        'password.*',
        'register.*',
        'auth.*',
        'verify-email',
        'verify-email.*',
        'admin.*',
        'subscription.expired',
        'collect.phone',
        'collect.phone.post',
        'invoices.pdf.public',
        'filter.property',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if ($request->is('admin*') || $request->is('auth/*')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        if (!auth()->user()->email_verified_at) {
            return redirect()->route('verify-email');
        }

        return $next($request);
    }
}