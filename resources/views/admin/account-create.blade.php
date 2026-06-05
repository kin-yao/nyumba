<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — Nyumba Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#111110}
        .layout{display:flex;min-height:100vh}
        .main{margin-left:220px;flex:1;padding:32px 40px}
        .back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#8a8880;text-decoration:none;margin-bottom:20px}
        .page-title{font-family:'DM Serif Display',serif;font-size:26px;margin-bottom:4px}
        .page-sub{font-size:13px;color:#8a8880;margin-bottom:28px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:24px;margin-bottom:20px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.06)}
        .form-group{margin-bottom:14px}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input,select{width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:#fff}
        input:focus,select:focus{border-color:#1a6b52}
        .btn{padding:9px 20px;font-size:13px;font-weight:500;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;border:none}
        .btn-green{background:#1a6b52;color:#fff}
        .error-msg{font-size:12px;color:#b91c1c;margin-top:4px}
        a{text-decoration:none;color:inherit}
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar', ['active' => 'accounts'])
    <main class="main">

        <a href="{{ route('admin.accounts') }}" class="back-link">
            ← All accounts
        </a>

        <div class="page-title">Create account</div>
        <div class="page-sub">Manually create a new landlord account with an owner user.</div>

        @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b">
                <div style="font-weight:500;margin-bottom:6px">Please fix the following:</div>
                @foreach($errors->all() as $error)
                    <div>&middot; {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.accounts.store') }}">
            @csrf

            {{-- Business details --}}
            <div class="card">
                <div class="card-title">Business details</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Business name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Kamau Properties">
                    </div>
                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="07XX or 01XX">
                    </div>
                    <div class="form-group">
                        <label>Email (optional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="business@email.com">
                    </div>
                    <div class="form-group">
                        <label>County</label>
                        <select name="county">
                            <option value="">Select county</option>
                            @foreach(['Nairobi','Mombasa','Kisumu','Nakuru','Kiambu','Machakos','Kajiado','Uasin Gishu','Kilifi','Meru'] as $county)
                                <option value="{{ $county }}" {{ old('county') === $county ? 'selected' : '' }}>{{ $county }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Currency</label>
                        <select name="currency">
                            <option value="KES" {{ old('currency','KES') === 'KES' ? 'selected' : '' }}>KES — Kenyan Shilling</option>
                            <option value="TZS" {{ old('currency') === 'TZS' ? 'selected' : '' }}>TZS — Tanzanian Shilling</option>
                            <option value="UGX" {{ old('currency') === 'UGX' ? 'selected' : '' }}>UGX — Ugandan Shilling</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Subscription --}}
            <div class="card">
                <div class="card-title">Subscription</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Plan</label>
                        <select name="plan" id="plan-select" onchange="updateLimits(this.value)">
                            @foreach(['explore','starter','growth','pro','enterprise'] as $p)
                                <option value="{{ $p }}" {{ old('plan','explore') === $p ? 'selected' : '' }}>
                                    {{ ucfirst($p) }}
                                    @if($p==='explore') (Free) @endif
                                    @if($p==='starter') (KES 2,000/mo, 20 units) @endif
                                    @if($p==='growth')  (KES 4,500/mo, 50 units) @endif
                                    @if($p==='pro')     (KES 7,000/mo, 100 units) @endif
                                    @if($p==='enterprise') (Unlimited) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Plan expiry date</label>
                        <input type="date" name="plan_expires_at" value="{{ old('plan_expires_at') }}"
                               placeholder="Leave blank for explore/trial">
                        <div style="font-size:11px;color:#8a8880;margin-top:3px">Leave blank for explore/trial accounts</div>
                    </div>
                </div>
            </div>

            {{-- Owner user --}}
            <div class="card">
                <div class="card-title">Owner user (login credentials)</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Full name</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required placeholder="e.g. John Kamau">
                    </div>
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}" required placeholder="owner@email.com">
                    </div>
                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="text" name="owner_phone" value="{{ old('owner_phone') }}" required placeholder="07XX">
                    </div>
                    <div class="form-group">
                        <label>Temporary password</label>
                        <input type="text" name="owner_password" value="{{ old('owner_password', 'password123') }}" required placeholder="Min 6 characters">
                        <div style="font-size:11px;color:#8a8880;margin-top:3px">Tell the owner to change this after first login</div>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-green">Create account</button>
                <a href="{{ route('admin.accounts') }}"
                   style="padding:9px 16px;font-size:13px;color:#8a8880;border:1px solid rgba(0,0,0,.1);border-radius:8px;background:#fff">
                    Cancel
                </a>
            </div>
        </form>
    </main>
</div>
</body>
</html>