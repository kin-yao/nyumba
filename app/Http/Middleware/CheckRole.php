<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // Routes only owners can access
    protected array $ownerOnly = [
        'settings.*',
        'audit.index',
        'subscription.*',
        'admin.*',
    ];

    // Routes all roles can access
    protected array $allRoles = [
        'properties.*',
        'units.*',
        'maintenance.*',
        'notifications.*',
        'filter.property',
        'health',
        'invoices.pdf.public',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Nyumba super admins bypass everything
        if ($user->is_admin) {
            return $next($request);
        }

        // Owners have full access
        if ($user->isOwner()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        // Always-allowed for any authenticated user
        foreach ($this->allRoles as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        // Caretaker — block everything except allRoles
        if ($user->isCaretaker()) {
            if ($routeName === 'dashboard') {
                return redirect()->route('properties.index');
            }
            return redirect()->route('properties.index')
                ->with('error', 'You do not have permission to access that page.');
        }

        // Manager — block owner-only routes
        if ($user->isManager()) {
            foreach ($this->ownerOnly as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return redirect()->route('dashboard')
                        ->with('error', 'You do not have permission to access that page.');
                }
            }
            return $next($request);
        }

        return $next($request);
    }
}