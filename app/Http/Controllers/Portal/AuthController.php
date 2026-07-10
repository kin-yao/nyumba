<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDevice;
use App\Models\TenantOtpVerification;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('portal_tenant_id')) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $normalized = $this->normalizePhone($validated['phone']);
        $tenants    = $this->findTenantsByPhone($normalized);

        if ($tenants->isEmpty()) {
            return back()->withInput()->withErrors([
                'phone' => 'We could not find an active tenancy for that phone number. Please contact your landlord if you believe this is a mistake.',
            ]);
        }

        // Rate limit — max one OTP request per 60 seconds per phone
        $recent = TenantOtpVerification::where('phone', $normalized)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return back()->withInput()->withErrors([
                'phone' => 'Please wait a minute before requesting another code.',
            ]);
        }

        // The OTP SMS itself only needs to go out once regardless of how many
        // tenancies this phone matches — bill it to the first landlord found.
        $billingTenant = $tenants->first();
        $account       = $billingTenant->account;

        if (!$account) {
            return back()->withInput()->withErrors([
                'phone' => 'Something went wrong. Please contact your landlord.',
            ]);
        }

        ['code' => $code] = TenantOtpVerification::createForPhone($normalized);

        if (app()->environment('local')) {
            // No SMS provider hooked up yet in dev — skip sending (and the
            // SMS-credit check below) and show the code directly instead.
            session(['portal_login_phone' => $normalized, 'dev_otp_code' => $code]);
            return redirect()->route('portal.verify');
        }

        $sms = new SmsService($account);

        if (!$sms->hasCredits()) {
            return back()->withInput()->withErrors([
                'phone' => 'Login codes are temporarily unavailable. Please contact your landlord.',
            ]);
        }

        $sms->send($billingTenant->phone, 'Your Nyumba login code is ' . $code . '. It expires in 10 minutes. Do not share this code.', $billingTenant->id);

        session(['portal_login_phone' => $normalized]);

        return redirect()->route('portal.verify');
    }

    public function showVerify()
    {
        if (!session('portal_login_phone')) {
            return redirect()->route('portal.login');
        }

        return view('portal.auth.verify');
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $normalized = session('portal_login_phone');

        if (!$normalized) {
            return redirect()->route('portal.login');
        }

        $otp = TenantOtpVerification::where('phone', $normalized)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired()) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if ($otp->isLocked()) {
            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new code.']);
        }

        if (!$otp->checkCode($validated['code'])) {
            $otp->increment('attempts');
            return back()->withErrors(['code' => 'Incorrect code. Please try again.']);
        }

        $otp->update(['verified_at' => now()]);

        $tenants = $this->findTenantsByPhone($normalized);

        if ($tenants->isEmpty()) {
            return redirect()->route('portal.login')->withErrors([
                'phone' => 'We could not find your tenancy. Please contact your landlord.',
            ]);
        }

        session()->forget('portal_login_phone');

        // Exactly one tenancy matched this phone — log straight in.
        if ($tenants->count() === 1) {
            return $this->finalizeLogin($tenants->first(), $request, $request->boolean('remember_device'));
        }

        // More than one active tenancy shares this phone number (e.g. the
        // same person renting from two different landlords) — let them pick.
        session([
            'portal_tenant_candidates' => $tenants->pluck('id')->all(),
            'portal_remember_device'   => $request->boolean('remember_device'),
        ]);

        return redirect()->route('portal.select-tenancy');
    }

    public function resendOtp(Request $request)
    {
        $normalized = session('portal_login_phone');

        if (!$normalized) {
            return redirect()->route('portal.login');
        }

        $recent = TenantOtpVerification::where('phone', $normalized)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return back()->withErrors(['code' => 'Please wait a minute before requesting another code.']);
        }

        $tenants = $this->findTenantsByPhone($normalized);

        if ($tenants->isEmpty()) {
            return redirect()->route('portal.login')->withErrors([
                'phone' => 'We could not find your tenancy. Please contact your landlord.',
            ]);
        }

        $billingTenant = $tenants->first();
        $account       = $billingTenant->account;

        ['code' => $code] = TenantOtpVerification::createForPhone($normalized);

        if (app()->environment('local')) {
            session(['dev_otp_code' => $code]);
            return redirect()->route('portal.verify')->with('success', 'New code generated.');
        }

        if (!$account) {
            return back()->withErrors(['code' => 'Something went wrong. Please contact your landlord.']);
        }

        $sms = new SmsService($account);

        if (!$sms->hasCredits()) {
            return back()->withErrors(['code' => 'Login codes are temporarily unavailable. Please contact your landlord.']);
        }

        $sms->send($billingTenant->phone, 'Your Nyumba login code is ' . $code . '. It expires in 10 minutes. Do not share this code.', $billingTenant->id);

        return redirect()->route('portal.verify')->with('success', 'A new code has been sent.');
    }

    public function showSelectTenancy()
    {
        $candidateIds = session('portal_tenant_candidates');

        if (!$candidateIds) {
            return redirect()->route('portal.login');
        }

        $tenants = Tenant::with(['activeLease.unit.property.account'])
            ->whereIn('id', $candidateIds)
            ->whereHas('leases', fn($q) => $q->where('status', 'active'))
            ->get();

        if ($tenants->isEmpty()) {
            session()->forget(['portal_tenant_candidates', 'portal_remember_device']);
            return redirect()->route('portal.login');
        }

        return view('portal.auth.select-tenancy', compact('tenants'));
    }

    public function selectTenancy(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
        ]);

        $candidateIds = session('portal_tenant_candidates', []);

        // Only allow picking from the exact set that matched the verified
        // phone number — never trust the posted tenant_id blindly.
        if (!in_array((int) $validated['tenant_id'], $candidateIds, true)) {
            return redirect()->route('portal.login')->withErrors([
                'phone' => 'Something went wrong. Please sign in again.',
            ]);
        }

        $tenant = Tenant::whereHas('leases', fn($q) => $q->where('status', 'active'))
            ->find($validated['tenant_id']);

        if (!$tenant) {
            return redirect()->route('portal.login')->withErrors([
                'phone' => 'That tenancy is no longer active. Please contact your landlord.',
            ]);
        }

        $rememberDevice = session('portal_remember_device', false);
        session()->forget(['portal_tenant_candidates', 'portal_remember_device']);

        return $this->finalizeLogin($tenant, $request, $rememberDevice);
    }

    public function logout(Request $request)
    {
        session()->forget(['portal_tenant_id', 'portal_login_phone', 'portal_tenant_candidates', 'portal_remember_device']);

        return redirect()->route('portal.login')
            ->withCookie(cookie()->forget('nyumba_tenant_device'));
    }

    private function finalizeLogin(Tenant $tenant, Request $request, bool $rememberDevice)
    {
        session(['portal_tenant_id' => $tenant->id]);

        $response = redirect()->route('portal.dashboard');

        if ($rememberDevice) {
            $token     = Str::random(64);
            $tokenHash = Hash::make($token);

            TenantDevice::create([
                'tenant_id'    => $tenant->id,
                'token_hash'   => $tokenHash,
                'user_agent'   => substr($request->userAgent() ?? '', 0, 255),
                'ip_address'   => $request->ip(),
                'last_used_at' => now(),
                'expires_at'   => now()->addDays(30),
            ]);

            $response->withCookie(cookie(
                'nyumba_tenant_device',
                $token,
                60 * 24 * 30, // minutes — 30 days
                null, null, true, true, false, 'Lax'
            ));
        }

        return $response;
    }

    /**
     * All tenants (across every landlord account) with an active lease whose
     * phone number matches. Normally this is one, but the same phone can
     * legitimately be an active tenant under more than one landlord.
     */
    private function findTenantsByPhone(string $normalized)
    {
        return Tenant::whereHas('leases', fn($q) => $q->where('status', 'active'))
            ->get()
            ->filter(fn($t) => $this->normalizePhone($t->phone) === $normalized)
            ->values();
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return substr($digits, -9); // last 9 significant digits
    }
}