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

        // Expired — dashboard only, with a clear notice
        if ($account->isExpired()) {
            $msg = 'Your subscription has expired. Upgrade to restore access to this feature.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 402);
            }
            return redirect()->route('dashboard')->with('subscription_notice', $msg);
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