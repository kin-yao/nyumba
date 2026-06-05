<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add your phone — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:460px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .steps{display:flex;gap:6px;margin:14px 0 28px}
        .step{height:3px;border-radius:2px;flex:1}
        .step.done{background:#1a6b52;opacity:.4}
        .step.active{background:#1a6b52}
        .step.inactive{background:#ece9e2}
        h1{font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:6px}
        .sub{font-size:13px;color:#8a8880;margin-bottom:20px}
        .google-badge{display:flex;align-items:center;gap:10px;background:#f5f4f0;border-radius:10px;padding:10px 14px;margin-bottom:22px}
        .google-badge .name{font-size:13px;font-weight:500}
        .google-badge .email{font-size:11px;color:#8a8880}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#1a6b52}
        .hint{font-size:11px;color:#8a8880;margin-top:4px}
        .error{font-size:12px;color:#b91c1c;margin-top:4px}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:18px;transition:background .2s}
        .btn:hover{background:#155c45}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" style="height:44px;object-fit:contain;display:block">
    <div class="steps">
        <div class="step done"></div>
        <div class="step active"></div>
        <div class="step inactive"></div>
        <div class="step inactive"></div>
    </div>

    <h1>Add your phone number</h1>
    <p class="sub">We use this to send you rent collection reports and account alerts via SMS.</p>

    @if(session('firebase.name'))
        <div class="google-badge">
            <div>
                <div class="name">{{ session('firebase.name') }}</div>
                <div class="email">Signed in with Google &middot; {{ session('firebase.email') }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register.phone.post') }}">
        @csrf
        <div>
            <label>Phone number</label>
            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="07XX or 254XXXXXXXXX" required autofocus>
            <div class="hint">Used for SMS reports and notifications only</div>
            @error('phone') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn">Continue →</button>
    </form>
</div>
</body>
</html>