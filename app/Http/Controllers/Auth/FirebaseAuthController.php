<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    // ─── Single verify endpoint ────────────────────────────────────────────────
    public function verify(Request $request)
    {
        $request->validate([
            'id_token'     => ['required', 'string'],
            'provider'     => ['required', 'in:email,google,phone'],
            'intent'       => ['required', 'in:login,register'],
            'name'         => ['nullable', 'string', 'max:255'],
            'first_name'   => ['nullable', 'string', 'max:255'],
            'last_name'    => ['nullable', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'accept_terms' => ['required_if:intent,register', 'accepted_if:intent,register'],
        ], [
            'accept_terms.required_if' => 'Please accept the Terms of Service and Privacy Policy to continue.',
            'accept_terms.accepted_if' => 'Please accept the Terms of Service and Privacy Policy to continue.',
        ]);

        $claims = $this->decodeJwtPayload($request->id_token);

        if (!$claims) {
            return response()->json(['error' => 'Session expired. Please try again.'], 401);
        }

        $uid           = $claims['sub'] ?? null;
        $email         = $claims['email'] ?? null;
        $phone         = $claims['phone_number'] ?? $request->phone;
        $name          = $claims['name'] ?? $request->name;
        $firstName     = $request->first_name ?? $claims['given_name'] ?? null;
        $lastName      = $request->last_name ?? $claims['family_name'] ?? null;
        $provider      = $request->provider;
        $intent        = $request->intent;
        $emailVerified = $claims['email_verified'] ?? false;

        if (!$uid) {
            return response()->json(['error' => 'Invalid token. Please try again.'], 401);
        }

        if (!$firstName && !$lastName && $name) {
            $parts     = explode(' ', trim($name), 2);
            $firstName = $parts[0] ?? null;
            $lastName  = $parts[1] ?? null;
        }

        // ── LOGIN ──────────────────────────────────────────────────────────────
        if ($intent === 'login') {

            $user = User::where('firebase_uid', $uid)->first()
                ?? ($email ? User::where('email', $email)->first() : null)
                ?? ($phone ? User::where('phone', $phone)->first() : null);

            if (!$user) {
                return response()->json([
                    'error' => 'No account found. Please register first.',
                ], 404);
            }

            // Admin users do not need a landlord account
            if (!$user->is_admin && !$user->account) {
                return response()->json([
                    'error' => 'This account no longer exists. Please register again.',
                ], 404);
            }

            if ($provider === 'email' && !$emailVerified) {
                return response()->json([
                    'error'   => 'verify_email',
                    'message' => 'Please verify your email address. Check your inbox.',
                ], 403);
            }

            if (!$user->firebase_uid) {
                $user->firebase_uid  = $uid;
                $user->auth_provider = $provider;
            }

            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }

            $user->save();
            Auth::login($user);
            session(['firebase_checked_at' => now()->timestamp]);

            $redirect = $user->is_admin
                ? route('admin.dashboard')
                : route('dashboard');

            return response()->json(['redirect' => $redirect]);
        }

        // ── REGISTER ───────────────────────────────────────────────────────────

        // Duplicate email check — only block if user has an active account
        if ($email) {
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                if ($existingUser->account) {
                    return response()->json([
                        'error' => 'An account with this email already exists. Please sign in instead.',
                    ], 409);
                }
                // Orphaned user (account was deleted) — clean up so they can re-register
                $existingUser->delete();
            }
        }

        // Duplicate phone check — only block if user has an active account
        $regPhone = $request->phone ?? $phone;
        if ($regPhone) {
            $existingUser = User::where('phone', $regPhone)->first();
            if ($existingUser) {
                if ($existingUser->account) {
                    return response()->json([
                        'error' => 'This phone number is already registered. Please sign in instead.',
                    ], 409);
                }
                $existingUser->delete();
            }
        }

        session([
            'firebase.uid'            => $uid,
            'firebase.email'          => $email,
            'firebase.phone'          => $regPhone,
            'firebase.name'           => $name,
            'firebase.first_name'     => $firstName,
            'firebase.last_name'      => $lastName,
            'firebase.provider'       => $provider,
            'firebase.email_verified' => $emailVerified || in_array($provider, ['google', 'phone']),
            'reg.name'                => $name ?? '',
            'reg.first_name'          => $firstName ?? '',
            'reg.last_name'           => $lastName ?? '',
            'reg.email'               => $email ?? '',
            'reg.phone'               => $regPhone ?? '',
            'reg.phone_verified'      => $provider === 'phone',
            'reg.accept_terms_at'     => now(),
        ]);

        if ($provider === 'google' && !$regPhone) {
            return response()->json([
                'status'   => 'new_user',
                'redirect' => route('register.phone'),
            ]);
        }

        if ($provider === 'email' && !$emailVerified) {
            return response()->json([
                'status'   => 'new_user',
                'redirect' => route('register.step2'),
            ]);
        }

        // We already have identity, phone, and terms acceptance — nothing
        // else to ask. Finish setting up the account right now.
        $user = $this->finishRegistration($uid, $email, $regPhone, $firstName, $lastName, $provider, $emailVerified);

        if (!$user) {
            return response()->json(['error' => 'Account creation failed. Please try again.'], 500);
        }

        return response()->json([
            'status'   => 'registered',
            'redirect' => route('dashboard'),
        ]);
    }

    // ─── Mark email verified ───────────────────────────────────────────────────
    public function markEmailVerified(Request $request)
    {
        $request->validate(['id_token' => ['required', 'string']]);

        $claims = $this->decodeJwtPayload($request->id_token);

        if (!$claims) {
            return response()->json(['error' => 'Invalid token. Please try again.'], 401);
        }

        if (!($claims['email_verified'] ?? false)) {
            return response()->json(['error' => 'Email not yet verified.'], 403);
        }

        $uid   = $claims['sub'] ?? null;
        $email = $claims['email'] ?? null;

        // Case 1: Already logged into Laravel
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->firebase_uid || $user->firebase_uid === $uid) {
                if (!$user->firebase_uid && $uid) $user->firebase_uid = $uid;
                $user->email_verified_at = now();
                $user->save();
                session(['firebase_checked_at' => now()->timestamp]);

                $redirect = $user->is_admin
                    ? route('admin.dashboard')
                    : route('dashboard');

                return response()->json(['redirect' => $redirect]);
            }
        }

        // Case 2: User exists but session expired
        if ($uid) {
            $user = User::where('firebase_uid', $uid)->first()
                ?? ($email ? User::where('email', $email)->first() : null);

            if ($user) {
                $user->update([
                    'email_verified_at' => now(),
                    'firebase_uid'      => $uid,
                ]);
                Auth::login($user);
                session(['firebase_checked_at' => now()->timestamp]);

                $redirect = $user->is_admin
                    ? route('admin.dashboard')
                    : route('dashboard');

                return response()->json(['redirect' => $redirect]);
            }
        }

        // Case 3: Mid-registration — we already have everything we need
        // (name, phone, terms acceptance were captured at step 1).
        $firstName = session('reg.first_name') ?: $claims['given_name'] ?? null;
        $lastName  = session('reg.last_name') ?: $claims['family_name'] ?? null;
        $phone     = session('reg.phone');

        $user = $this->finishRegistration(
            $uid,
            $email,
            $phone,
            $firstName,
            $lastName,
            'email',
            true
        );

        if (!$user) {
            return response()->json(['error' => 'Account creation failed. Please try again.'], 500);
        }

        return response()->json(['redirect' => route('dashboard')]);
    }

    // ─── Phone collection for Google signup ───────────────────────────────────
    public function showCollectPhone()
    {
        if (!session('firebase.uid')) {
            return redirect()->route('register.step1');
        }
        return view('auth.register.collect-phone');
    }

    public function storePhone(Request $request)
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        $existingUser = User::where('phone', $request->phone)->first();
        if ($existingUser && $existingUser->account) {
            return back()->withErrors([
                'phone' => 'This phone number is already registered. Please sign in instead.',
            ]);
        }
        if ($existingUser && !$existingUser->account) {
            $existingUser->delete();
        }

        session([
            'firebase.phone' => $request->phone,
            'reg.phone'      => $request->phone,
        ]);

        if (!session('firebase.uid')) {
            return redirect()->route('register.step1')
                ->withErrors(['error' => 'Session expired. Please start again.']);
        }

        $user = $this->finishRegistration(
            session('firebase.uid'),
            session('firebase.email'),
            $request->phone,
            session('reg.first_name'),
            session('reg.last_name'),
            'google',
            true
        );

        if (!$user) {
            return back()->withErrors(['error' => 'Account creation failed. Please try again.']);
        }

        return redirect()->route('dashboard');
    }

    // ─── Phone collection for logged-in users ─────────────────────────────────
    public function showCollectPhoneLoggedIn()
    {
        if (!auth()->check() || auth()->user()->phone) {
            return redirect()->route('dashboard');
        }
        return view('auth.collect-phone');
    }

    public function storePhoneLoggedIn(Request $request)
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);
        auth()->user()->update(['phone' => $request->phone]);
        return redirect()->route('dashboard')->with('success', 'Phone number saved.');
    }

    /**
     * Creates the Account + owner User once we have everything we need:
     * a verified identity (uid/email), a phone number, and terms acceptance.
     * Called directly from verify()/storePhone()/markEmailVerified() — there
     * is no separate "finish setting up" step anymore.
     */
    private function finishRegistration(
        string $uid,
        ?string $email,
        ?string $phone,
        ?string $firstName,
        ?string $lastName,
        string $provider,
        bool $emailVerified
    ) {
        $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: 'User';

        try {
            $user = null;

            DB::transaction(function () use (
                $uid, $email, $phone, $fullName, $provider, $emailVerified, &$user
            ) {
                // Always read from PLANS — never hardcode unit limits or SMS credits.
                $explorePlan = Account::PLANS['explore'];

                $account = Account::create([
                    'name'                 => $fullName . "'s Properties",
                    'phone'                => $phone ?? '',
                    'email'                => $email ?? '',
                    'plan'                 => 'explore',
                    'billing_cycle'        => 'monthly',
                    'unit_limit'           => $explorePlan['unit_limit'],
                    'sms_credits'          => $explorePlan['sms_credits_monthly'],
                    'sms_credits_monthly'  => $explorePlan['sms_credits_monthly'],
                    // trial_ends_at = the 30-day free trial window
                    // plan_expires_at = null until a paid plan is purchased via M-Pesa
                    'trial_ends_at'        => now()->addDays(30),
                    'plan_expires_at'      => null,
                    'use_case'             => 'own_rental',
                    'terms_accepted_at'    => session('reg.accept_terms_at', now()),
                    'unit_count_range'     => null,
                    'recommended_plan'     => 'starter',
                    'currency'             => 'KES',
                ]);

                $user = User::create([
                    'account_id'          => $account->id,
                    'firebase_uid'        => $uid,
                    'auth_provider'       => $provider,
                    'name'                => $fullName,
                    'email'               => $email ?? '',
                    'phone'               => $phone ?? '',
                    'email_verified_at'   => ($emailVerified || in_array($provider, ['google', 'phone'])) ? now() : null,
                    'phone_verified_at'   => now(),
                    'onboarding_complete' => true,
                    'role'                => 'owner',
                    'password'            => bcrypt(Str::random(32)),
                ]);

                \App\Models\Notification::create([
                    'account_id' => $account->id,
                    'type'       => 'welcome',
                    'title'      => 'Welcome to Nyumba!',
                    'body'       => 'Your 30 day free trial has started. You have '
                        . $explorePlan['sms_credits_monthly']
                        . ' SMS credits to get started. Add your first property to begin.',
                ]);

                Auth::login($user);
            });
        } catch (\Exception $e) {
            \Log::error('Account creation failed: ' . $e->getMessage());
            return null;
        }

        session()->forget([
            'firebase.uid', 'firebase.email', 'firebase.phone',
            'firebase.name', 'firebase.first_name', 'firebase.last_name', 'firebase.provider', 'firebase.email_verified',
            'reg.name', 'reg.first_name', 'reg.last_name', 'reg.email', 'reg.phone',
            'reg.phone_verified', 'reg.accept_terms_at',
        ]);

        session(['firebase_checked_at' => now()->timestamp]);

        return $user;
    }

    // ─── Forgot password page ──────────────────────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // ─── Decode JWT payload locally without any network call ──────────────────
    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        $payload = $parts[1];
        $payload = strtr($payload, '-_', '+/');
        $padded  = str_pad($payload, (int)(ceil(strlen($payload) / 4) * 4), '=');
        $decoded = json_decode(base64_decode($padded), true);

        if (!is_array($decoded)) return null;
        if (isset($decoded['exp']) && $decoded['exp'] < time()) return null;

        return $decoded;
    }
}