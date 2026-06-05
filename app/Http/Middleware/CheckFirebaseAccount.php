<?php

namespace App\Http\Middleware;

use App\Services\FirebaseService;
use Closure;
use Illuminate\Http\Request;

class CheckFirebaseAccount
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user->firebase_uid) {
            return $next($request);
        }

        $lastCheck = session('firebase_checked_at', 0);
        $elapsed   = now()->timestamp - $lastCheck;

        // Check once every 5 minutes instead of every 10 seconds
        if ($elapsed >= 300) {
            try {
                $firebase = app(FirebaseService::class);
                $exists   = $firebase->userExists($user->firebase_uid);

                if (!$exists) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors(['email' => 'This account no longer exists. Please register again.']);
                }

                session(['firebase_checked_at' => now()->timestamp]);

            } catch (\Exception $e) {
                \Log::warning('Firebase account check failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}