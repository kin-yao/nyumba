<x-layouts.portal>
@if(session('dev_otp_code'))
    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;text-align:center;font-size:13px;color:#92400e;margin-bottom:16px">
        Dev mode — your code is <strong>{{ session('dev_otp_code') }}</strong>
    </div>
@endif
<div style="padding-top:56px;text-align:center;margin-bottom:28px">
    <img src="/images/logo.png" alt="Nyumba" style="height:40px;width:auto;margin:0 auto 20px;display:block">
    <div style="font-family:'DM Serif Display',serif;font-size:22px">Enter code</div>
    <div style="font-size:13px;color:#8a8880;margin-top:4px">Sent by SMS</div>
</div>

<form method="POST" action="{{ route('portal.verify.submit') }}">
    @csrf
    <input type="text" name="code" required autofocus maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="000000"
           style="width:100%;height:52px;padding:0 14px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:22px;letter-spacing:0.3em;text-align:center;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box;margin-bottom:14px">

    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#8a8880;margin-bottom:18px">
        <input type="checkbox" name="remember_device" value="1" checked style="width:16px;height:16px;accent-color:#1a6b52">
        Remember this device
    </label>

    <button type="submit"
            style="width:100%;padding:13px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
        Verify
    </button>
</form>

@if(session('success'))
    <div style="background:#dcfce7;border-left:4px solid #166534;padding:10px 14px;text-align:center;font-size:13px;color:#166534;margin-bottom:16px">
        {{ session('success') }}
    </div>
@endif

<div style="text-align:center;margin-top:16px;display:flex;flex-direction:column;gap:10px">
    <form method="POST" action="{{ route('portal.verify.resend') }}">
        @csrf
        <button type="submit" style="background:none;border:none;font-size:12px;color:#0B6161;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
            Resend code
        </button>
    </form>
    <a href="{{ route('portal.login') }}" style="font-size:12px;color:#8a8880">Use a different number</a>
</div>
</x-layouts.portal>