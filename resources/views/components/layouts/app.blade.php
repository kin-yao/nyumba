<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Nyumba') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* ── Responsive base ── */
        *, *::before, *::after { box-sizing: border-box; }

        .nyumba-shell        { display:block; }
        .nyumba-sidebar      { position:fixed; top:0; left:0; bottom:0; width:220px; background:#111110; display:flex; flex-direction:column; transition:transform .25s ease; z-index:40; }
        .nyumba-main         { margin-left:220px; background:#f5f4f0; min-height:100vh; min-width:0; }
        .nyumba-topbar       { display:none; }
        .nyumba-overlay      { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:39; }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            .nyumba-sidebar      { transform: translateX(-100%); }
            .nyumba-sidebar.open { transform: translateX(0); }
            .nyumba-overlay.open { display: block; }
            .nyumba-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 16px;
                background: #111110;
                flex-shrink: 0;
                position: sticky;
                top: 0;
                z-index: 30;
            }
            .nyumba-main { margin-left: 0; }
        }

        /* ── Global responsive helpers ── */
        .tbl-wrap            { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 768px) {
            .hide-mobile     { display: none !important; }
            .page-pad        { padding: 16px !important; }
            .stat-grid       { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            .stat-grid-3     { grid-template-columns: 1fr 1fr !important; }
            .card-grid       { grid-template-columns: 1fr !important; }
            .form-grid-2     { grid-template-columns: 1fr !important; }
            .form-grid-3     { grid-template-columns: 1fr !important; }
            .flex-mobile-col { flex-direction: column !important; align-items: flex-start !important; }
            .gap-mobile      { gap: 8px !important; }
            .full-mobile     { width: 100% !important; }
            .text-sm-mobile  { font-size: 12px !important; }
            .modal-inner     { width: calc(100vw - 32px) !important; max-width: 100% !important; margin: 16px !important; }
            .settings-grid   { grid-template-columns: 1fr !important; }
        }

        @media (max-width: 480px) {
            .stat-grid       { grid-template-columns: 1fr !important; }
            .stat-grid-3     { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body style="font-family:'DM Sans',sans-serif;margin:0;padding:0">

{{-- Mobile overlay --}}
<div class="nyumba-overlay" id="mob-overlay" onclick="closeSidebar()"></div>

<div class="nyumba-shell">

    {{-- ── Sidebar ── --}}
    <aside class="nyumba-sidebar" id="sidebar">

        {{-- Logo --}}
        <div style="padding:16px 16px 14px;border-bottom:1px solid rgba(255,255,255,0.06)">
            <div style="background:#fff;border-radius:10px;padding:10px 12px;margin-bottom:6px">
                <img src="/images/logo.png" alt="Nyumba"
                     style="width:100%;height:auto;object-fit:contain;display:block">
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,0.28)">
                {{ auth()->user()->account->name ?? 'My Account' }}
            </div>
        </div>

        @php
            
            $account          = auth()->user()->account;
            $smsCredits       = $account->sms_credits ?? 0;

            // Cache notification count for 60 seconds per account
            $unreadCount = cache()->remember(
                'notif_count_' . auth()->user()->account_id, 60,
                fn() => \App\Models\Notification::where('account_id', auth()->user()->account_id)
                    ->unread()->count()
            );

            // Cache properties list for 5 minutes per account
            $allProperties = $account ? cache()->remember(
                'props_list_' . auth()->user()->account_id, 300,
                fn() => \App\Models\Property::where('account_id', auth()->user()->account_id)
                    ->orderBy('name')->get()
            ) : collect();

            $filterPropertyId = session('filter_property_id');
            $filterProperty   = $filterPropertyId ? $allProperties->firstWhere('id', $filterPropertyId) : null;

            $activeGroup = 'overview';
            if (request()->routeIs('properties.*') || request()->routeIs('tenants.*') || request()->routeIs('maintenance.*')) {
                $activeGroup = 'properties';
            } elseif (request()->routeIs('invoices.*') || request()->routeIs('payments.*') || request()->routeIs('expenses.*') || request()->routeIs('utilities.*') || request()->routeIs('reports.*')) {
                $activeGroup = 'financials';
            } elseif (request()->routeIs('communications.*')) {
                $activeGroup = 'communication';
            } elseif (request()->routeIs('notifications.*') || request()->routeIs('audit.*') || request()->routeIs('settings.*')) {
                $activeGroup = 'system';
            }
        @endphp

        {{-- Property filter --}}
        @if($allProperties->count() > 1)
            <div style="padding:10px 12px;border-bottom:1px solid rgba(255,255,255,0.06)">
                <div style="font-size:9px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,0.28);margin-bottom:5px">Viewing</div>
                <form method="POST" action="{{ route('filter.property') }}">
                    @csrf
                    <select name="property_id" onchange="this.form.submit()"
                            style="width:100%;background:#fff;border:1px solid rgba(255,255,255,0.1);border-radius:6px;color:#111110;font-size:12px;padding:6px 8px;font-family:'DM Sans',sans-serif;outline:none;cursor:pointer">
                        <option value="all" {{ !$filterPropertyId ? 'selected' : '' }}>All properties</option>
                        @foreach($allProperties as $prop)
                            <option value="{{ $prop->id }}" {{ $filterPropertyId == $prop->id ? 'selected' : '' }}>{{ $prop->name }}</option>
                        @endforeach
                    </select>
                </form>
                @if($filterProperty)
                    <div style="font-size:10px;color:#4ade80;margin-top:4px">● Filtered to {{ $filterProperty->name }}</div>
                @endif
            </div>
        @endif

        {{-- Navigation --}}
        <nav style="padding:6px 0;flex:1;overflow-y:auto">

            {{-- Dashboard --}}
            @php $active = request()->routeIs('dashboard'); @endphp
            <a href="{{ route('dashboard') }}" onclick="closeSidebar()"
               style="display:flex;align-items:center;gap:9px;padding:8px 18px;font-size:13px;text-decoration:none;white-space:nowrap;
               color:{{ $active ? '#fff' : 'rgba(255,255,255,0.55)' }};
               border-left:2px solid {{ $active ? '#1a6b52' : 'transparent' }};
               background:{{ $active ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                <span style="flex-shrink:0;opacity:{{ $active ? '1' : '0.6' }};display:flex">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <rect x="1" y="1" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                        <rect x="8" y="1" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                        <rect x="1" y="8" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                        <rect x="8" y="8" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                    </svg>
                </span>
                Dashboard
            </a>

            {{-- Properties group --}}
            <div id="group-properties">
                <button onclick="toggleGroup('properties')"
                        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:8px 18px;background:{{ $activeGroup==='properties' ? 'rgba(255,255,255,0.04)' : 'transparent' }};border:none;cursor:pointer;font-family:'DM Sans',sans-serif">
                    <div style="display:flex;align-items:center;gap:9px">
                        <span style="opacity:0.6;display:flex;flex-shrink:0">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1L1 6v7h3.5V9.5h5V13H13V6L7 1z" stroke="white" stroke-width="1.2" stroke-linejoin="round"/></svg>
                        </span>
                        <span style="font-size:13px;color:{{ $activeGroup==='properties' ? '#fff' : 'rgba(255,255,255,0.55)' }}">Properties</span>
                    </div>
                    <svg id="chevron-properties" width="12" height="12" viewBox="0 0 12 12" fill="none"
                         style="flex-shrink:0;opacity:0.4;transition:transform .2s;transform:{{ $activeGroup==='properties' ? 'rotate(180deg)' : 'rotate(0deg)' }}">
                        <path d="M2.5 4.5l3.5 3 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div id="items-properties" style="display:{{ $activeGroup==='properties' ? 'block' : 'none' }}">
                    @foreach([
                        ['properties.index', 'Properties',  'properties.*'],
                        ['tenants.index',    'Tenants',     'tenants.*'],
                        ['maintenance.index','Maintenance', 'maintenance.*'],
                    ] as [$route, $label, $pattern])
                        @php $a = request()->routeIs($pattern); @endphp
                        <a href="{{ route($route) }}" onclick="closeSidebar()"
                           style="display:flex;align-items:center;padding:7px 18px 7px 40px;font-size:12.5px;text-decoration:none;
                           color:{{ $a ? '#fff' : 'rgba(255,255,255,0.45)' }};
                           border-left:2px solid {{ $a ? '#1a6b52' : 'transparent' }};
                           background:{{ $a ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Financials group --}}
            <div id="group-financials">
                <button onclick="toggleGroup('financials')"
                        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:8px 18px;background:{{ $activeGroup==='financials' ? 'rgba(255,255,255,0.04)' : 'transparent' }};border:none;cursor:pointer;font-family:'DM Sans',sans-serif">
                    <div style="display:flex;align-items:center;gap:9px">
                        <span style="opacity:0.6;display:flex;flex-shrink:0">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3.5" width="12" height="8" rx="1.5" stroke="white" stroke-width="1.2"/><path d="M1 6.5h12" stroke="white" stroke-width="1.2"/><circle cx="4" cy="9.5" r=".8" fill="white"/></svg>
                        </span>
                        <span style="font-size:13px;color:{{ $activeGroup==='financials' ? '#fff' : 'rgba(255,255,255,0.55)' }}">Financials</span>
                    </div>
                    <svg id="chevron-financials" width="12" height="12" viewBox="0 0 12 12" fill="none"
                         style="flex-shrink:0;opacity:0.4;transition:transform .2s;transform:{{ $activeGroup==='financials' ? 'rotate(180deg)' : 'rotate(0deg)' }}">
                        <path d="M2.5 4.5l3.5 3 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div id="items-financials" style="display:{{ $activeGroup==='financials' ? 'block' : 'none' }}">
                    @foreach([
                        ['invoices.index',  'Invoices',  'invoices.*'],
                        ['payments.index',  'Payments',  'payments.*'],
                        ['expenses.index',  'Expenses',  'expenses.*'],
                        ['utilities.index', 'Utilities', 'utilities.*'],
                        ['reports.index',   'Reports',   'reports.*'],
                    ] as [$route, $label, $pattern])
                        @php $a = request()->routeIs($pattern); @endphp
                        <a href="{{ route($route) }}" onclick="closeSidebar()"
                           style="display:flex;align-items:center;padding:7px 18px 7px 40px;font-size:12.5px;text-decoration:none;
                           color:{{ $a ? '#fff' : 'rgba(255,255,255,0.45)' }};
                           border-left:2px solid {{ $a ? '#1a6b52' : 'transparent' }};
                           background:{{ $a ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Communication group --}}
            <div id="group-communication">
                <button onclick="toggleGroup('communication')"
                        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:8px 18px;background:{{ $activeGroup==='communication' ? 'rgba(255,255,255,0.04)' : 'transparent' }};border:none;cursor:pointer;font-family:'DM Sans',sans-serif">
                    <div style="display:flex;align-items:center;gap:9px">
                        <span style="opacity:0.6;display:flex;flex-shrink:0">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 2h10a1 1 0 011 1v6a1 1 0 01-1 1H4L1 12V3a1 1 0 011-1z" stroke="white" stroke-width="1.2" stroke-linejoin="round"/></svg>
                        </span>
                        <span style="font-size:13px;color:{{ $activeGroup==='communication' ? '#fff' : 'rgba(255,255,255,0.55)' }}">Communication</span>
                    </div>
                    <svg id="chevron-communication" width="12" height="12" viewBox="0 0 12 12" fill="none"
                         style="flex-shrink:0;opacity:0.4;transition:transform .2s;transform:{{ $activeGroup==='communication' ? 'rotate(180deg)' : 'rotate(0deg)' }}">
                        <path d="M2.5 4.5l3.5 3 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div id="items-communication" style="display:{{ $activeGroup==='communication' ? 'block' : 'none' }}">
                    @php $a = request()->routeIs('communications.*'); @endphp
                    <a href="{{ route('communications.index') }}" onclick="closeSidebar()"
                       style="display:flex;align-items:center;padding:7px 18px 7px 40px;font-size:12.5px;text-decoration:none;
                       color:{{ $a ? '#fff' : 'rgba(255,255,255,0.45)' }};
                       border-left:2px solid {{ $a ? '#1a6b52' : 'transparent' }};
                       background:{{ $a ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                        SMS &amp; Messages
                    </a>
                </div>
            </div>

            {{-- System group --}}
            <div id="group-system">
                <button onclick="toggleGroup('system')"
                        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:8px 18px;background:{{ $activeGroup==='system' ? 'rgba(255,255,255,0.04)' : 'transparent' }};border:none;cursor:pointer;font-family:'DM Sans',sans-serif">
                    <div style="display:flex;align-items:center;gap:9px">
                        <span style="opacity:0.6;display:flex;flex-shrink:0">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="2" stroke="white" stroke-width="1.2"/><path d="M7 1v2M7 11v2M1 7h2M11 7h2M2.8 2.8l1.4 1.4M9.8 9.8l1.4 1.4M2.8 11.2l1.4-1.4M9.8 4.2l1.4-1.4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                        </span>
                        <span style="font-size:13px;color:{{ $activeGroup==='system' ? '#fff' : 'rgba(255,255,255,0.55)' }}">System</span>
                    </div>
                    <svg id="chevron-system" width="12" height="12" viewBox="0 0 12 12" fill="none"
                         style="flex-shrink:0;opacity:0.4;transition:transform .2s;transform:{{ $activeGroup==='system' ? 'rotate(180deg)' : 'rotate(0deg)' }}">
                        <path d="M2.5 4.5l3.5 3 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div id="items-system" style="display:{{ $activeGroup==='system' ? 'block' : 'none' }}">
                    @php $a = request()->routeIs('notifications.*'); @endphp
                    <a href="{{ route('notifications.index') }}" onclick="closeSidebar()"
                       style="display:flex;align-items:center;justify-content:space-between;padding:7px 18px 7px 40px;font-size:12.5px;text-decoration:none;
                       color:{{ $a ? '#fff' : 'rgba(255,255,255,0.45)' }};
                       border-left:2px solid {{ $a ? '#1a6b52' : 'transparent' }};
                       background:{{ $a ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                        Notifications
                        @if($unreadCount > 0)
                            <span style="background:#b91c1c;color:#fff;font-size:10px;font-weight:600;padding:1px 6px;border-radius:10px;min-width:18px;text-align:center;flex-shrink:0">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    @foreach([
                        ['audit.index',    'Audit trail', 'audit.*'],
                        ['settings.index', 'Settings',    'settings.*'],
                    ] as [$route, $label, $pattern])
                        @php $a = request()->routeIs($pattern); @endphp
                        <a href="{{ route($route) }}" onclick="closeSidebar()"
                           style="display:flex;align-items:center;padding:7px 18px 7px 40px;font-size:12.5px;text-decoration:none;
                           color:{{ $a ? '#fff' : 'rgba(255,255,255,0.45)' }};
                           border-left:2px solid {{ $a ? '#1a6b52' : 'transparent' }};
                           background:{{ $a ? 'rgba(255,255,255,0.06)' : 'transparent' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

        </nav>

        {{-- Low SMS credits warning --}}
        @if($account && $smsCredits <= 20)
            <div style="margin:0 10px 10px;background:#fee2e2;border-radius:8px;padding:10px 12px">
                <div style="font-size:11px;font-weight:500;color:#991b1b;margin-bottom:2px">SMS credits low</div>
                <div style="font-size:11px;color:#b91c1c">{{ $smsCredits }} credits remaining</div>
            </div>
        @endif

        {{-- User footer --}}
        <div style="padding:14px 18px;border-top:1px solid rgba(255,255,255,0.06)">
            <div style="display:flex;align-items:center;gap:9px">
                <div style="width:30px;height:30px;border-radius:50%;background:#1a6b52;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.68);line-height:1.3">{{ auth()->user()->name }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="font-size:11px;color:rgba(255,255,255,0.28);background:none;border:none;cursor:pointer;padding:0;font-family:'DM Sans',sans-serif">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <main class="nyumba-main">

        {{-- Mobile top bar --}}
        <div class="nyumba-topbar">
            <button onclick="openSidebar()"
                    style="background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;justify-content:center">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                    <path d="M3 6h16M3 11h16M3 16h16" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
            <div style="background:#fff;border-radius:8px;padding:6px 10px">
                <img src="/images/logo.png" alt="Nyumba" style="height:28px;width:auto;object-fit:contain;display:block">
            </div>
            <a href="{{ route('notifications.index') }}" style="position:relative;padding:4px;display:flex">
                <svg width="20" height="20" viewBox="0 0 14 14" fill="none">
                    <path d="M7 1a4 4 0 014 4v3l1 1.5H2L3 8V5a4 4 0 014-4z" stroke="white" stroke-width="1.2" stroke-linejoin="round"/>
                    <path d="M5.5 11.5a1.5 1.5 0 003 0" stroke="white" stroke-width="1.2"/>
                </svg>
                @if($unreadCount > 0)
                    <span style="position:absolute;top:0;right:0;background:#b91c1c;color:#fff;font-size:9px;font-weight:700;width:14px;height:14px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </a>
        </div>

        {{-- Banners --}}
        @if(session('impersonating_account_id'))
            <div style="background:#1e40af;color:#fff;padding:10px 20px;font-size:13px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span>👁 Viewing as <strong>{{ auth()->user()->account->name ?? 'Unknown' }}</strong></span>
                <form method="POST" action="{{ route('admin.stop-impersonating') }}" style="margin:0">
                    @csrf
                    <button type="submit" style="background:rgba(255,255,255,.2);border:none;color:#fff;font-size:12px;padding:4px 12px;border-radius:5px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Exit impersonation
                    </button>
                </form>
            </div>
        @endif

        @if($account && $account->isOnTrial())
            @php $daysLeft = $account->trialDaysRemaining(); @endphp
            <div style="background:#1a6b52;color:#fff;padding:10px 20px;font-size:13px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span>🎉 Free trial — <strong>{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} remaining</strong></span>
                <a href="{{ route('settings.index') }}" style="color:#fff;font-weight:500;font-size:12px;background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:6px;text-decoration:none;white-space:nowrap">View plans</a>
            </div>
        @endif

        @if($account && $account->isInGracePeriod())
            @php $graceDays = $account->graceDaysRemaining(); @endphp
            <div style="background:#d97706;color:#fff;padding:10px 20px;font-size:13px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span>⚠ Subscription expired — <strong>{{ $graceDays }} {{ Str::plural('day', $graceDays) }} grace period left</strong></span>
                <a href="{{ route('settings.index') }}" style="color:#fff;font-weight:500;font-size:12px;background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:6px;text-decoration:none;white-space:nowrap">Renew now</a>
            </div>
        @endif

        @if($account && $account->isActive() && $account->plan !== 'explore')
            @php $daysToExpiry = $account->subscriptionDaysRemaining(); @endphp
            @if($daysToExpiry <= 7 && $daysToExpiry > 0)
                <div style="background:#fef3c7;border-bottom:1px solid #fcd34d;color:#92400e;padding:10px 20px;font-size:13px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                    <span>Subscription expires in <strong>{{ $daysToExpiry }} {{ Str::plural('day', $daysToExpiry) }}</strong></span>
                    <a href="{{ route('settings.index') }}" style="color:#92400e;font-weight:500;font-size:12px;background:rgba(0,0,0,0.08);padding:4px 12px;border-radius:6px;text-decoration:none;white-space:nowrap">Renew now</a>
                </div>
            @endif
        @endif

        @if($filterProperty)
            <div style="background:#1a6b52;color:#fff;padding:8px 20px;font-size:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span>Showing: <strong>{{ $filterProperty->name }}</strong></span>
                <form method="POST" action="{{ route('filter.property') }}" style="margin:0">
                    @csrf
                    <input type="hidden" name="property_id" value="all">
                    <button type="submit" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:11px;padding:3px 10px;border-radius:5px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Clear filter
                    </button>
                </form>
            </div>
        @endif

        {{ $slot }}
    </main>
</div>

<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('mob-overlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('mob-overlay').classList.remove('open');
        document.body.style.overflow = '';
    }
    function toggleGroup(name) {
        const items   = document.getElementById('items-' + name);
        const chevron = document.getElementById('chevron-' + name);
        const isOpen  = window.getComputedStyle(items).display !== 'none';
        items.style.display     = isOpen ? 'none' : 'block';
        chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }
</script>

@livewireScripts
</body>
</html>