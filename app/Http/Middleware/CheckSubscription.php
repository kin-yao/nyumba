<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected array $except = [
        'dashboard',
        'settings.*',
        'notifications.*',
        'logout',
        'subscription.expired',
        'admin.*',
        'verify.*',
        'verify-email',
        'collect.phone',
        'collect.phone.post',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // Always allow admin routes
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

        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        // Account fully expired and grace period over
        if ($account->isExpired()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription expired.'], 402);
            }
            return redirect()->route('subscription.expired');
        }

        // Grace period — allow read only, block write actions
        if ($account->isInGracePeriod()) {
            $writeRoutes = [
                'invoices.store',
                'invoices.bulk.store',
                'invoices.bulk.preview',
                'invoices.destroy',
                'payments.store',
                'tenants.store',
                'properties.store',
                'units.store',
                'utilities.store',
                'expenses.store',
                'maintenance.store',
                'communications.send',
            ];

            if (in_array($routeName, $writeRoutes)) {
                return redirect()->back()
                    ->with('error', 'Your subscription is in the grace period. You have '
                        . $account->graceDaysRemaining()
                        . ' days to renew before your account is locked.');
            }
        }

        // Explore plan feature restrictions
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
                    ->with('error', 'This feature is not available on the Explore plan. Upgrade to access bulk invoicing and PDF downloads.');
            }
        }

        return $next($request);
    }
}