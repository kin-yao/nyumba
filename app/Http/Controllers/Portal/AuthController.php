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

        $tenant = Tenant::whereHas('leases', fn($q) => $q->where('status', 'active'))
            ->get()
            ->first(fn($t) => $this->normalizePhone($t->phone) === $normalized);

        if (!$tenant) {
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

        $account = $tenant->account;

        if (!$account) {
            return back()->withInput()->withErrors([
                'phone' => 'Something went wrong. Please contact your landlord.',
            ]);
        }

        ['code' => $code] = TenantOtpVerification::createForPhone($normalized);

        if (app()->environment('local')) {
            // No SMS provider hooked up yet in dev — skip sending, show the
            // code directly instead so the OTP flow can still be tested.
            session(['portal_login_phone' => $normalized, 'dev_otp_code' => $code]);
            return redirect()->route('portal.verify');
        }

        $sms = new SmsService($account);

        if (!$sms->hasCredits()) {
            return back()->withInput()->withErrors([
                'phone' => 'Login codes are temporarily unavailable. Please contact your landlord.',
            ]);
        }

        $sms->send($tenant->phone, 'Your Nyumba login code is ' . $code . '. It expires in 10 minutes. Do not share this code.', $tenant->id);

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

        $tenant = Tenant::whereHas('leases', fn($q) => $q->where('status', 'active'))
            ->get()
            ->first(fn($t) => $this->normalizePhone($t->phone) === $normalized);

        if (!$tenant) {
            return redirect()->route('portal.login')->withErrors([
                'phone' => 'We could not find your tenancy. Please contact your landlord.',
            ]);
        }

        session(['portal_tenant_id' => $tenant->id]);
        session()->forget('portal_login_phone');

        $response = redirect()->route('portal.dashboard');

        if ($request->boolean('remember_device')) {
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

    public function logout(Request $request)
    {
        session()->forget(['portal_tenant_id', 'portal_login_phone']);

        return redirect()->route('portal.login')
            ->withCookie(cookie()->forget('nyumba_tenant_device'));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return substr($digits, -9); // last 9 significant digits
    }
}