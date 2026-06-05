<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    // Always accessible regardless of subscription status
    protected array $always = [
        'dashboard',
        'logout',
        'notifications.index',
        'notifications.read',
        'notifications.read-all',
        'filter.property',
        'subscription.expired',
        'admin.*',
        'verify.*',
        'verify-email',
        'collect.phone',
        'collect.phone.post',
        'invoices.pdf.public',
        'audit.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // Admin users bypass all subscription checks
        if (auth()->user()->is_admin) {
            return $next($request);
        }

        if ($request->is('admin*')) {
            return $next($request);
        }

        $user    = auth()->user();
        $account = $user->account;

        // Account deleted — log out immediately
        if (!$account) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deleted.']);
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        // Check always-allowed routes first
        foreach ($this->always as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        // Expired — dashboard only, everything else comes back here
        if ($account->isExpired()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription expired. Please upgrade to continue.'], 402);
            }
            return redirect()->route('dashboard');
        }

        // Explore plan feature restrictions (active trial only)
        if ($account->plan === 'explore') {
            $blockedRoutes = [
                'invoices.bulk',
                'invoices.bulk.store',
                'invoices.bulk.preview',
                'invoices.bulk.preview.show',
                'invoices.pdf',
            ];

            if (in_array($routeName, $blockedRoutes)) {
                return redirect()->route('dashboard')
                    ->with('error', 'Bulk invoicing and PDF downloads are not available on the free trial. Upgrade to access these features.');
            }
        }

        return $next($request);
    }
}