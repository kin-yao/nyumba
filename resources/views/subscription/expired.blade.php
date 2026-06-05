<x-layouts.app>
    <div style="padding:60px 34px;max-width:600px;margin:0 auto;text-align:center">

        <div style="font-size:48px;margin-bottom:16px">🔒</div>

        <div style="font-family:'DM Serif Display',serif;font-size:28px;margin-bottom:8px">
            Your subscription has expired
        </div>

        <div style="font-size:14px;color:#8a8880;margin-bottom:32px;line-height:1.6">
            Your account has been locked. Your data is safe and will be retained
            for 90 days. Renew your subscription to restore full access.
        </div>

        {{-- Plans --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px;text-align:left">

            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
                <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:8px">Starter</div>
                <div style="font-family:'DM Serif Display',serif;font-size:24px;margin-bottom:4px">KES 2,000</div>
                <div style="font-size:11px;color:#8a8880;margin-bottom:12px">per month</div>
                <div style="font-size:12px;display:grid;gap:4px;color:#8a8880">
                    <div>Up to 20 units</div>
                    <div>50 SMS credits/month</div>
                </div>
            </div>

            <div style="background:#fff;border-radius:10px;border:2px solid #1a6b52;padding:20px;position:relative">
                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#1a6b52;color:#fff;font-size:10px;font-weight:600;padding:2px 10px;border-radius:10px;white-space:nowrap">
                    MOST POPULAR
                </div>
                <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:8px">Growth</div>
                <div style="font-family:'DM Serif Display',serif;font-size:24px;margin-bottom:4px">KES 4,500</div>
                <div style="font-size:11px;color:#8a8880;margin-bottom:12px">per month</div>
                <div style="font-size:12px;display:grid;gap:4px;color:#8a8880">
                    <div>Up to 50 units</div>
                    <div>100 SMS credits/month</div>
                </div>
            </div>

            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
                <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:8px">Pro</div>
                <div style="font-family:'DM Serif Display',serif;font-size:24px;margin-bottom:4px">KES 7,000</div>
                <div style="font-size:11px;color:#8a8880;margin-bottom:12px">per month</div>
                <div style="font-size:12px;display:grid;gap:4px;color:#8a8880">
                    <div>Up to 100 units</div>
                    <div>200 SMS credits/month</div>
                </div>
            </div>
        </div>

        <div style="background:#f5f4f0;border-radius:10px;padding:20px;margin-bottom:24px;font-size:13px;color:#8a8880">
            <div style="font-weight:500;color:#111110;margin-bottom:6px">How to renew</div>
            <div style="line-height:1.7">
                Send your renewal payment via M-Pesa to our team and we will
                activate your account within minutes. Contact us on
                <strong style="color:#111110">{{ config('app.support_phone', '0700 000 000') }}</strong>
                or email
                <strong style="color:#111110">{{ config('app.support_email', 'support@nyumba.co.ke') }}</strong>
            </div>
        </div>

        <div style="font-size:12px;color:#8a8880">
            Pay 6 months, get 1 free &middot; Pay 12 months, get 2 free
        </div>

        <div style="margin-top:24px">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        style="font-size:13px;color:#8a8880;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:underline">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>