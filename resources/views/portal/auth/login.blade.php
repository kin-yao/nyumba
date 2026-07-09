<x-layouts.portal>
<div style="padding-top:56px;text-align:center;margin-bottom:36px">
    <img src="/images/logo.png" alt="Nyumba" style="height:56px;width:auto;margin:0 auto 22px;display:block">
    <div style="font-family:'DM Serif Display',serif;font-size:22px">Sign in</div>
</div>

<form method="POST" action="{{ route('portal.login.send') }}">
    @csrf
    <input type="text" name="phone" required autofocus placeholder="Phone number" value="{{ old('phone') }}"
           style="width:100%;height:46px;padding:0 14px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:16px;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box;margin-bottom:12px">

    <button type="submit"
            style="width:100%;padding:13px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
        Continue
    </button>
</form>
</x-layouts.portal>