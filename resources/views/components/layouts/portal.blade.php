<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Nyumba') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #f5f4f0; font-family: 'DM Sans', sans-serif; color: #111110; }
        .portal-shell { min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 72px; }
        .portal-topbar {
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .portal-logo { height: 22px; width: auto; display: block; }
        .portal-logout {
            background: none;
            border: none;
            color: #8a8880;
            font-size: 12px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
        }
        .portal-content { flex: 1; padding: 4px 16px 8px; max-width: 480px; margin: 0 auto; width: 100%; }
        .portal-bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid rgba(0,0,0,0.08);
            display: flex;
            z-index: 30;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .portal-nav-item {
            flex: 1;
            text-align: center;
            padding: 10px 4px 8px;
            text-decoration: none;
            color: #8a8880;
            font-size: 11px;
            font-weight: 500;
        }
        .portal-nav-item.active { color: #1a6b52; }
        .portal-nav-icon { display: block; margin: 0 auto 3px; height: 19px; width: 19px; object-fit: contain; }
        .portal-nav-item.active .portal-nav-icon { filter: invert(29%) sepia(45%) saturate(682%) hue-rotate(115deg) brightness(94%) contrast(92%); }
        .portal-flash {
            border-radius: 10px;
            padding: 11px 15px;
            font-size: 13px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
<div class="portal-shell">
    <div class="portal-topbar">
        @if(session('portal_tenant_id'))
            <img src="/images/logo.png" alt="Nyumba" class="portal-logo">
        @else
            <span></span>
        @endif
        @if(session('portal_tenant_id'))
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="portal-logout">Sign out</button>
            </form>
        @endif
    </div>

    <div class="portal-content">
        @if(session('success'))
            <div class="portal-flash" style="background:#dcfce7;border:1px solid #86efac;color:#166534">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="portal-flash" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="portal-flash" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b">
                {{ $errors->first() }}
            </div>
        @endif

        {{ $slot }}
    </div>

    @if(session('portal_tenant_id'))
        <div class="portal-bottom-nav">
            <a href="{{ route('portal.dashboard') }}" class="portal-nav-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <img src="/images/portal/icon-home.png" class="portal-nav-icon" alt="Home"> Home
            </a>
            <a href="{{ route('portal.payment') }}" class="portal-nav-item {{ request()->routeIs('portal.payment') ? 'active' : '' }}">
                <img src="/images/portal/icon-payment.png" class="portal-nav-icon" alt="Payment"> Payment
            </a>
            <a href="{{ route('portal.communications') }}" class="portal-nav-item {{ request()->routeIs('portal.communications') ? 'active' : '' }}">
                <img src="/images/portal/icon-requests.png" class="portal-nav-icon" alt="Requests"> Requests
            </a>
        </div>
    @endif
</div>
</body>
</html>