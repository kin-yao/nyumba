<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add your phone number — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:460px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .logo img{height:48px;object-fit:contain;margin-bottom:28px;display:block}
        h1{font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:8px}
        .subtitle{font-size:13px;color:#8a8880;margin-bottom:24px;line-height:1.6}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#1a6b52}
        .hint{font-size:11px;color:#8a8880;margin-top:4px}
        .error{font-size:12px;color:#b91c1c;margin-top:4px}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:18px;transition:background .2s}
        .btn:hover{background:#155c45}
        .skip{display:block;text-align:center;margin-top:14px;font-size:13px;color:#8a8880;cursor:pointer;text-decoration:none}
        .skip:hover{color:#111110}
    </style>
</head>
<body>
    <div class="card">
        <img src="/images/logo.png" alt="Nyumba" style="height:48px;object-fit:contain;display:block;margin-bottom:28px">

        <h1>One more thing</h1>
        <p class="subtitle">
            We need your phone number to send you rent collection reports and account alerts.
            Without it you will miss important SMS notifications.
        </p>

        @if(session('success'))
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#166534">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('collect.phone.post') }}">
            @csrf
            <div>
                <label>Phone number</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       placeholder="07XX or 254XXXXXXXXX" required autofocus>
                <div class="hint">Used for SMS reports and account notifications only</div>
                @error('phone') <div class="error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn">Save and continue →</button>
        </form>

        <a href="{{ route('dashboard') }}" class="skip">Skip for now</a>
    </div>
</body>
</html>