<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    // Reachable even when the account is fully expired — just enough to see
    // the dashboard lockout screen and pay via M-Pesa to restore access.
    protected array $alwaysDuringExpiry = [
        'dashboard',
        'logout',
        'subscription.expired',
        'subscription.upgrade',
        'subscription.status',
        'admin.*',
        'verify.*',
        'verify-email',
        'collect.phone',
        'collect.phone.post',
        'invoices.pdf.public',
    ];

    // Reachable whenever the account is active/on trial/in grace period
    // (used only for the explore-plan feature restrictions below)
    protected array $always = [
        'notifications.index',
        'notifications.read',
        'notifications.read-all',
        'filter.property',
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

        // Expired — nothing is reachable except the dashboard lockout screen
        // and the M-Pesa upgrade flow used to restore access.
        if ($account->isExpired()) {
            foreach ($this->alwaysDuringExpiry as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return $next($request);
                }
            }

            $msg = 'Your subscription has expired. Pay via M-Pesa on the dashboard to restore access.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 402);
            }
            return redirect()->route('dashboard')->with('subscription_notice', $msg);
        }

        // Not expired — always-allowed utility routes
        foreach ($this->always as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }
        foreach ($this->alwaysDuringExpiry as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        // Explore (free trial) feature restrictions
        if ($account->plan === 'explore') {
            $blockedRoutes = [
                'invoices.bulk',
                'invoices.bulk.store',
                'invoices.bulk.preview',
                'invoices.bulk.preview.show',
                'invoices.pdf',
            ];

            if (in_array($routeName, $blockedRoutes)) {
                $msg = $routeName === 'invoices.pdf'
                    ? 'Downloading invoice PDFs isn’t available on the free trial. Upgrade to download and share PDF invoices.'
                    : 'Bulk invoicing isn’t available on the free trial. Upgrade to generate invoices in bulk.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $msg], 402);
                }

                return back()->with('subscription_notice', $msg);
            }
        }

        return $next($request);
    }
}