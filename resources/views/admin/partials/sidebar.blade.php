@php($active = $active ?? '')

<style>
    .sidebar{width:220px;background:#111110;flex-shrink:0;display:flex;flex-direction:column;position:fixed;top:0;bottom:0}
    .sidebar-logo{padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.06)}
    .sidebar-logo .logo-box{background:#fff;border-radius:10px;padding:10px 12px;display:block;margin-bottom:8px}
    .sidebar-logo .logo-box img{width:100%;height:auto;object-fit:contain;display:block}
    .sidebar-logo-sub{font-size:11px;color:rgba(255,255,255,.28);text-align:center}
    .sidebar-nav{padding:10px 0;flex:1;overflow-y:auto}
    .nav-item{display:flex;align-items:center;gap:9px;padding:9px 18px;font-size:13px;text-decoration:none;color:rgba(255,255,255,.48);border-left:2px solid transparent;transition:all .2s}
    .nav-item:hover{color:#fff;background:rgba(255,255,255,.04)}
    .nav-item.active{color:#fff;border-left-color:#1a6b52;background:rgba(255,255,255,.06)}
    .nav-item.disabled{color:rgba(255,255,255,.2);cursor:default;pointer-events:none}
    .nav-section{font-size:10px;color:rgba(255,255,255,.2);letter-spacing:.06em;text-transform:uppercase;padding:12px 18px 4px}
    .sidebar-footer{padding:14px 18px;border-top:1px solid rgba(255,255,255,.06);font-size:12px;color:rgba(255,255,255,.35)}
</style>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Nyumba">
        </div>
        <div class="sidebar-logo-sub">Admin panel</div>
    </div>

    <nav class="sidebar-nav">

        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <rect x="1" y="1" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/>
                <rect x="8" y="1" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/>
                <rect x="1" y="8" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/>
                <rect x="8" y="8" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('admin.accounts') }}" class="nav-item {{ $active === 'accounts' ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="4" r="2.5" stroke="currentColor" stroke-width="1.2"/>
                <path d="M1.5 13c0-3 2.5-4.5 5.5-4.5s5.5 1.5 5.5 4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            Accounts
        </a>

        <a href="{{ route('admin.broadcast') }}" class="nav-item {{ $active === 'broadcast' ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M1 3.5h12v7a1 1 0 01-1 1H2a1 1 0 01-1-1v-7z" stroke="currentColor" stroke-width="1.2"/>
                <path d="M1 3.5l6 4.5 6-4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Broadcast SMS
        </a>

        <div class="nav-section">Coming soon</div>

        <span class="nav-item disabled">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <rect x="1" y="3" width="12" height="8" rx="1" stroke="currentColor" stroke-width="1.2"/>
                <path d="M1 6h12" stroke="currentColor" stroke-width="1.2"/>
            </svg>
            Daraja / M-Pesa
        </span>

        <span class="nav-item disabled">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.2"/>
                <path d="M7 4v3l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            SMS credit store
        </span>

        <div class="nav-section">Session</div>

        <a href="{{ route('dashboard') }}" class="nav-item">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M3 7h8M8 4l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back to app
        </a>

    </nav>

    <div class="sidebar-footer">Logged in as {{ auth()->user()->name }}</div>
</aside>