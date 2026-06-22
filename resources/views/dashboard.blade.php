<x-layouts.app>
<style>
/* ── Token System ── */
:root {
    --ink:          #0d1117;
    --ink-2:        #374151;
    --mute:         #6b7280;
    --mute-2:       #9ca3af;
    --paper:        #f5f6f8;
    --card:         #ffffff;
    --line:         #e5e7eb;
    --line-2:       #f3f4f6;

    --brand:        #0e3f30;
    --brand-mid:    #1a6b52;
    --brand-light:  #e8f5f0;
    --brand-accent: #22c55e;

    --gold:         #b45309;
    --gold-bg:      #fffbeb;
    --gold-border:  #fde68a;

    --red:          #dc2626;
    --red-bg:       #fef2f2;
    --red-border:   #fecaca;

    --blue:         #1d4ed8;
    --blue-bg:      #eff6ff;

    --radius-sm:    6px;
    --radius-md:    10px;
    --radius-lg:    14px;
    --radius-xl:    18px;

    --shadow-sm:    0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md:    0 4px 12px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.04);
}

* { box-sizing: border-box; }

.db { padding: 0 0 80px; background: var(--paper); min-height: 100vh; font-family: 'DM Sans', sans-serif; }

/* ── Page header ── */
.db-page-head {
    padding: 28px clamp(16px,3vw,32px) 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.db-page-head-left {}
.db-greeting {
    font-family: 'DM Serif Display', serif;
    font-size: clamp(20px,2.8vw,26px);
    color: var(--ink);
    line-height: 1.2;
    margin: 0 0 4px;
}
.db-subline {
    font-size: 13px;
    color: var(--mute);
    margin: 0;
}
.db-month-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--brand-mid);
    background: var(--brand-light);
    padding: 4px 12px;
    border-radius: 20px;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ── Alerts strip ── */
.db-alerts { padding: 0 clamp(16px,3vw,32px) 8px; display: flex; flex-direction: column; gap: 8px; }
.db-alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 16px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
    flex-wrap: wrap;
}
.db-alert-link {
    font-size: 12px;
    font-weight: 600;
    padding: 3px 12px;
    border-radius: 20px;
    text-decoration: none;
    flex-shrink: 0;
}

/* ── Metric strip (top hero row) ── */
.db-metric-strip {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    padding: 0 clamp(16px,3vw,32px) 12px;
}
.db-metric {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: inherit;
    display: block;
    transition: box-shadow .15s, transform .15s;
}
.db-metric:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.db-metric-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}
.db-metric-value {
    font-family: 'DM Serif Display', serif;
    font-size: 28px;
    line-height: 1;
    margin-bottom: 4px;
}
.db-metric-label {
    font-size: 12px;
    color: var(--mute);
    font-weight: 500;
}
.db-metric-sub {
    font-size: 11px;
    color: var(--mute-2);
    margin-top: 2px;
}

/* ── Revenue hero ── */
.db-revenue-hero {
    background: var(--brand);
    margin: 0 clamp(16px,3vw,32px) 16px;
    border-radius: var(--radius-xl);
    overflow: hidden;
    position: relative;
}
.db-revenue-hero-noise {
    position: absolute; inset: 0; pointer-events: none; z-index: 0;
}
.db-revenue-hero-inner {
    position: relative; z-index: 1;
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 0;
}
.db-donut-col {
    padding: 28px 20px 28px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-right: 1px solid rgba(255,255,255,.08);
}
.db-donut-legend {
    display: flex;
    gap: 14px;
    margin-top: 12px;
}
.db-donut-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: rgba(255,255,255,.5);
}
.db-donut-legend-dot {
    width: 7px; height: 7px; border-radius: 50%;
}
.db-revenue-stats {
    padding: 28px 28px 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.db-revenue-kpis {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
}
.db-rev-kpi {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: var(--radius-md);
    padding: 14px 16px;
}
.db-rev-kpi-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.db-rev-kpi-value {
    font-family: 'DM Serif Display', serif;
    font-size: clamp(16px,2vw,20px);
    line-height: 1;
    color: #fff;
}
/* ── Quick stats bar ── */
.db-quick-bar {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    border-top: 1px solid rgba(255,255,255,.08);
}
.db-quick-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    text-decoration: none;
    border-right: 1px solid rgba(255,255,255,.08);
    transition: background .15s;
    color: inherit;
}
.db-quick-item:last-child { border-right: none; }
.db-quick-item:hover { background: rgba(255,255,255,.05); }
.db-quick-ico {
    width: 30px; height: 30px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.db-quick-num { font-size: 18px; font-weight: 700; color: #fff; line-height: 1; }
.db-quick-lbl { font-size: 11px; color: rgba(255,255,255,.45); margin-top: 2px; }

/* ── Section layout ── */
.db-section { padding: 0 clamp(16px,3vw,32px); }
.db-section + .db-section { margin-top: 16px; }

/* ── Section label ── */
.db-section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--mute-2);
    margin: 0 0 10px;
}

/* ── Two col ── */
.db-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ── Card ── */
.db-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.db-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--line-2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.db-card-title { font-size: 13px; font-weight: 600; color: var(--ink); margin: 0; }
.db-card-link {
    font-size: 12px;
    font-weight: 600;
    color: var(--brand-mid);
    text-decoration: none;
    background: var(--brand-light);
    padding: 3px 10px;
    border-radius: 20px;
    flex-shrink: 0;
    transition: background .15s;
}
.db-card-link:hover { background: #c6e8da; }

/* ── Payment row ── */
.db-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 20px;
    border-bottom: 1px solid var(--line-2);
    transition: background .1s;
}
.db-row:last-child { border-bottom: none; }
.db-row:hover { background: var(--paper); }
.db-avatar {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.db-row-name { font-size: 13px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.db-row-sub { font-size: 11px; color: var(--mute); margin-top: 1px; }
.db-amount-positive { font-size: 13px; font-weight: 700; color: #15803d; flex-shrink: 0; }
.db-amount-negative { font-size: 13px; font-weight: 700; color: var(--red); flex-shrink: 0; }

/* Remind button */
.db-remind-btn {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 12px;
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 20px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.db-remind-btn:hover { background: #ffedd5; }

/* ── Chart card ── */
.db-chart-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-top: 14px;
}
.db-chart-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--line-2);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.db-chart-totals { display: flex; gap: 24px; flex-wrap: wrap; }
.db-chart-total { text-align: right; }
.db-chart-total-label { font-size: 11px; color: var(--mute); margin-bottom: 3px; }
.db-chart-total-val { font-size: 14px; font-weight: 700; }
.db-chart-body { padding: 20px 22px; }
.db-chart-legend { display: flex; gap: 16px; margin-bottom: 16px; }
.db-chart-legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--mute); }
.db-chart-legend-dot { width: 10px; height: 10px; border-radius: 3px; }

/* ── Bar chart ── */
.db-bars { display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; height: 180px; padding-bottom: 28px; }
.db-bar-group { flex: 1; display: flex; flex-direction: column; align-items: center; }
.db-bar-pair { display: flex; gap: 3px; align-items: flex-end; width: 100%; justify-content: center; margin-bottom: 8px; }
.db-bar { flex: 1; max-width: 20px; border-radius: 3px 3px 0 0; min-height: 0; }
.db-bar-label { font-size: 10px; font-weight: 500; color: var(--mute-2); }

/* ── Properties list ── */
.db-prop-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 13px 20px;
    border-bottom: 1px solid var(--line-2);
    text-decoration: none;
    color: inherit;
    transition: background .12s;
}
.db-prop-row:last-child { border-bottom: none; }
.db-prop-row:hover { background: var(--paper); }
.db-prop-name { font-size: 13px; font-weight: 600; color: var(--ink); }
.db-prop-sub { font-size: 12px; color: var(--mute); margin-top: 2px; }
.db-prop-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    flex-shrink: 0;
}

/* ── Empty states ── */
.db-empty {
    padding: 40px 20px;
    text-align: center;
    color: var(--mute);
    font-size: 13px;
}
.db-empty-icon { font-size: 26px; margin-bottom: 8px; }

/* ── Expired / flash ── */
.db-expired {
    background: var(--card);
    border: 2px solid var(--red);
    border-radius: var(--radius-lg);
    padding: 32px 24px;
    text-align: center;
    margin: 0 clamp(16px,3vw,32px) 20px;
}
.db-flash {
    border-radius: var(--radius-md);
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
}

/* ── Responsive ── */
@media (max-width: 960px) {
    .db-metric-strip { grid-template-columns: 1fr 1fr; }
    .db-metric-strip .db-metric:nth-child(3) { display: none; }
    .db-revenue-hero-inner { grid-template-columns: 1fr; }
    .db-donut-col { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); padding: 24px 24px 20px; flex-direction: row; gap: 24px; }
    .db-revenue-stats { padding: 0 24px 0; }
    .db-revenue-kpis { grid-template-columns: 1fr 1fr 1fr; }
}
@media (max-width: 768px) {
    .db-2col { grid-template-columns: 1fr; }
    .db-metric-strip { grid-template-columns: 1fr 1fr 1fr; }
    .db-metric-strip .db-metric:nth-child(3) { display: block; }
    .db-revenue-kpis { grid-template-columns: 1fr 1fr 1fr; }
    .db-donut-col { flex-direction: column; }
    .db-quick-bar { grid-template-columns: 1fr 1fr 1fr; }
}
@media (max-width: 520px) {
    .db-metric-strip { grid-template-columns: 1fr 1fr; }
    .db-metric-strip .db-metric:last-child { display: none; }
    .db-revenue-kpis { grid-template-columns: 1fr; }
    .db-revenue-hero-inner { grid-template-columns: 1fr; }
    .db-quick-bar { grid-template-columns: 1fr; }
    .db-quick-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); }
    .db-quick-item:last-child { border-bottom: none; }
    .db-chart-totals { gap: 14px; }
    .db-chart-total { text-align: left; }
}
</style>

<div class="db">

@php $account = auth()->user()->account; @endphp

{{-- ── Expired banner ── --}}
@if($account && $account->isExpired())
<div class="db-expired">
    <div style="font-size:30px;margin-bottom:10px">🔒</div>
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--ink);margin-bottom:6px">
        @if($account->plan==='explore') Free trial ended
        @else {{ ucfirst($account->plan) }} plan expired
        @endif
    </div>
    <div style="font-size:13px;color:var(--mute);margin-bottom:20px">Your data is safe. Upgrade to restore access.</div>
    <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
       target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:#25D366;color:#fff;border-radius:var(--radius-md);font-size:14px;font-weight:600;text-decoration:none">
        WhatsApp us to upgrade
    </a>
</div>
@endif

{{-- ── Flash messages ── --}}
@if(session('success'))
<div style="margin:0 clamp(16px,3vw,32px) 10px">
    <div class="db-flash" style="background:var(--brand-light);border:1px solid #a7d7c0;color:var(--brand-mid)">
        {{ session('success') }}
    </div>
</div>
@endif
@if(session('error'))
<div style="margin:0 clamp(16px,3vw,32px) 10px">
    <div class="db-flash" style="background:var(--red-bg);border:1px solid var(--red-border);color:var(--red)">
        {{ session('error') }}
    </div>
</div>
@endif

{{-- ── Alerts ── --}}
@if($urgentMaintenance > 0 || $overdueCount > 0)
<div class="db-alerts">
    @if($urgentMaintenance > 0)
    <div class="db-alert" style="background:var(--red-bg);border:1px solid var(--red-border);color:var(--red)">
        <span style="display:flex;align-items:center;gap:8px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/></svg>
            {{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request',$urgentMaintenance) }} need attention
        </span>
        <a href="{{ route('maintenance.index') }}" class="db-alert-link" style="background:#fee2e2;color:var(--red)">View requests</a>
    </div>
    @endif
    @if($overdueCount > 0)
    <div class="db-alert" style="background:var(--gold-bg);border:1px solid var(--gold-border);color:var(--gold)">
        <span style="display:flex;align-items:center;gap:8px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ $overdueCount }} overdue {{ Str::plural('invoice',$overdueCount) }}
        </span>
        <a href="{{ route('invoices.index') }}" class="db-alert-link" style="background:var(--gold-border);color:var(--gold)">View invoices</a>
    </div>
    @endif
</div>
@endif

{{-- ── Page header ── --}}
<div class="db-page-head">
    <div class="db-page-head-left">
        <p class="db-greeting">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ',auth()->user()->name)[0] }}
        </p>
        <p class="db-subline">Here's what's happening with your portfolio today.</p>
        <div class="db-month-badge">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }}
        </div>
    </div>
</div>

{{-- ── KPI metric strip ── --}}
<div class="db-metric-strip">
    <a href="{{ route('properties.index') }}" class="db-metric">
        <div class="db-metric-icon" style="background:var(--brand-light)">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--brand-mid)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="db-metric-value" style="color:var(--ink)">{{ $totalProperties }}</div>
        <div class="db-metric-label">Properties</div>
    </a>

    <a href="{{ route('tenants.index') }}" class="db-metric">
        <div class="db-metric-icon" style="background:var(--blue-bg)">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="db-metric-value" style="color:var(--ink)">{{ $totalTenants }}</div>
        <div class="db-metric-label">Active tenants</div>
    </a>

    <a href="{{ route('reports.index') }}" class="db-metric">
        <div class="db-metric-icon" style="background:{{ $netProfitThisMonth >= 0 ? 'var(--brand-light)' : 'var(--red-bg)' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $netProfitThisMonth >= 0 ? 'var(--brand-mid)' : 'var(--red)' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="db-metric-value" style="color:{{ $netProfitThisMonth >= 0 ? 'var(--brand-mid)' : 'var(--red)' }}">
            {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
        </div>
        <div class="db-metric-label">Net profit this month</div>
    </a>
</div>

{{-- ── Revenue hero panel ── --}}
@php
    $totalExpected  = $expectedThisMonth > 0 ? $expectedThisMonth : 1;
    $collectedPct   = min(100, ($collectedThisMonth / $totalExpected) * 100);
    $outstandingPct = max(0, 100 - $collectedPct);
    $showEmpty = $expectedThisMonth == 0;
    $r = 72; $cx = 90; $cy = 90;
    $circ = 2 * M_PI * $r;
    $collectedArc   = ($collectedPct  / 100) * $circ;
    $collectedGap   = $circ - $collectedArc;
    $outstandingArc = ($outstandingPct / 100) * $circ;
    $outstandingGap = $circ - $outstandingArc;
    $outstandingOffset = -$collectedArc;
@endphp

<div style="padding: 0 clamp(16px,3vw,32px)">
<div class="db-revenue-hero">
    {{-- Subtle geometric accent --}}
    <div class="db-revenue-hero-noise" aria-hidden="true">
        <svg width="100%" height="100%" viewBox="0 0 900 300" preserveAspectRatio="xMidYMid slice">
            <circle cx="760" cy="-40" r="200" fill="rgba(255,255,255,.03)"/>
            <circle cx="820" cy="260" r="120" fill="rgba(255,255,255,.025)"/>
            <polygon points="0,0 320,0 180,300 0,300" fill="rgba(255,255,255,.02)"/>
        </svg>
    </div>

    <div class="db-revenue-hero-inner">
        {{-- Donut ── --}}
        <div class="db-donut-col">
            <svg width="180" height="180" viewBox="0 0 180 180" role="img" aria-label="Collection rate {{ $collectionRate }}%">
                <title>Collection rate: {{ $collectionRate }}%</title>
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="24"/>
                @if($showEmpty)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.14)" stroke-width="24"/>
                @else
                    @if($outstandingPct > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#ef4444" stroke-width="24"
                            stroke-dasharray="{{ $outstandingArc }} {{ $outstandingGap }}"
                            stroke-dashoffset="{{ $outstandingOffset }}"
                            transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                    @endif
                    @if($collectedPct > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#22c55e" stroke-width="24"
                            stroke-dasharray="{{ $collectedArc }} {{ $collectedGap }}"
                            transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                    @endif
                @endif
                <text x="{{ $cx }}" y="{{ $cy - 10 }}" text-anchor="middle" font-family="DM Serif Display,serif" font-size="28" font-weight="700" fill="#fff">{{ $collectionRate }}%</text>
                <text x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" fill="rgba(255,255,255,.45)" letter-spacing="2.5">COLLECTED</text>
            </svg>
            <div class="db-donut-legend">
                <div class="db-donut-legend-item"><div class="db-donut-legend-dot" style="background:#22c55e"></div> Collected</div>
                <div class="db-donut-legend-item"><div class="db-donut-legend-dot" style="background:#ef4444"></div> Outstanding</div>
            </div>
        </div>

        {{-- Revenue stats ── --}}
        <div class="db-revenue-stats">
            <div style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:14px">
                {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }} revenue
            </div>
            <div class="db-revenue-kpis">
                <div class="db-rev-kpi">
                    <div class="db-rev-kpi-label" style="color:#22c55e">Collected</div>
                    <div class="db-rev-kpi-value">{{ currency($collectedThisMonth) }}</div>
                </div>
                <div class="db-rev-kpi">
                    <div class="db-rev-kpi-label" style="color:#f87171">Outstanding</div>
                    <div class="db-rev-kpi-value">{{ currency($outstandingThisMonth) }}</div>
                </div>
                <div class="db-rev-kpi">
                    <div class="db-rev-kpi-label" style="color:rgba(255,255,255,.5)">Expected</div>
                    <div class="db-rev-kpi-value">{{ currency($expectedThisMonth) }}</div>
                </div>
            </div>

            {{-- Occupancy bar ── --}}
            <div style="margin-bottom:24px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <div style="font-size:11px;color:rgba(255,255,255,.45)">Occupancy</div>
                    <div style="font-size:12px;font-weight:700;color:#fff">{{ $occupancyRate }}%
                        <span style="font-size:11px;font-weight:400;color:rgba(255,255,255,.4)">· {{ $vacantUnits }} vacant</span>
                    </div>
                </div>
                <div style="height:5px;background:rgba(255,255,255,.1);border-radius:10px;overflow:hidden">
                    <div style="height:100%;width:{{ $occupancyRate }}%;background:{{ $occupancyRate >= 80 ? '#22c55e' : '#f59e0b' }};border-radius:10px;transition:width .4s"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick stat bar ── --}}
    <div class="db-quick-bar">
        <a href="{{ route('maintenance.index') }}" class="db-quick-item">
            <div class="db-quick-ico" style="background:{{ $openMaintenance > 0 ? 'rgba(251,146,60,.18)' : 'rgba(34,197,94,.12)' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $openMaintenance > 0 ? '#fb923c' : '#22c55e' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
            </div>
            <div>
                <div class="db-quick-num" style="color:{{ $openMaintenance > 0 ? '#fb923c' : '#fff' }}">{{ $openMaintenance }}</div>
                <div class="db-quick-lbl">Open maintenance</div>
            </div>
        </a>
        <a href="{{ route('invoices.index') }}" class="db-quick-item">
            <div class="db-quick-ico" style="background:{{ $overdueCount > 0 ? 'rgba(239,68,68,.18)' : 'rgba(34,197,94,.12)' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $overdueCount > 0 ? '#ef4444' : '#22c55e' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
            </div>
            <div>
                <div class="db-quick-num" style="color:{{ $overdueCount > 0 ? '#ef4444' : '#fff' }}">{{ $overdueCount }}</div>
                <div class="db-quick-lbl">Overdue invoices</div>
            </div>
        </a>
        <a href="{{ route('tenants.index') }}" class="db-quick-item">
            <div class="db-quick-ico" style="background:rgba(167,139,250,.15)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
            </div>
            <div>
                <div class="db-quick-num">{{ $occupiedUnits }}</div>
                <div class="db-quick-lbl">Occupied units</div>
            </div>
        </a>
    </div>
</div>
</div>

{{-- ── Payments + Outstanding ── --}}
<div class="db-section" style="margin-top:20px">
    <p class="db-section-label">Activity</p>
    <div class="db-2col">

        {{-- Recent payments ── --}}
        <div class="db-card">
            <div class="db-card-head">
                <p class="db-card-title">Recent payments</p>
                <a href="{{ route('payments.index') }}" class="db-card-link">View all</a>
            </div>
            @if($recentPayments->isEmpty())
                <div class="db-empty">
                    <div class="db-empty-icon">💳</div>
                    No payments recorded yet
                </div>
            @else
                @foreach($recentPayments as $pmt)
                <div class="db-row">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
                        <div class="db-avatar" style="background:var(--brand-light);color:var(--brand-mid)">
                            {{ $pmt->tenant ? strtoupper(substr($pmt->tenant->first_name,0,1).substr($pmt->tenant->last_name,0,1)) : '?' }}
                        </div>
                        <div style="min-width:0">
                            <div class="db-row-name">{{ $pmt->tenant?->full_name ?? 'Unknown' }}</div>
                            <div class="db-row-sub">{{ $pmt->payment_date->format('d M') }} &middot; {{ strtoupper($pmt->method) }}</div>
                        </div>
                    </div>
                    <div class="db-amount-positive">{{ currency($pmt->amount) }}</div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Outstanding balances ── --}}
        <div class="db-card">
            <div class="db-card-head">
                <p class="db-card-title">Outstanding balances</p>
                <a href="{{ route('reports.outstanding') }}" class="db-card-link">Full report</a>
            </div>
            @if($tenantsWithBalance->isEmpty())
                <div class="db-empty">
                    <div class="db-empty-icon">✅</div>
                    All tenants are up to date
                </div>
            @else
                @foreach($tenantsWithBalance as $item)
                <div class="db-row">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div class="db-avatar" style="background:var(--red-bg);color:var(--red)">
                            {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                        </div>
                        <div style="min-width:0">
                            <div class="db-row-name">{{ $item['tenant']->full_name }}</div>
                            <div class="db-row-sub">{{ $item['unit']->name }} &middot; {{ $item['property']->name }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                        <div class="db-amount-negative">{{ currency($item['balance']) }}</div>
                        <form method="POST" action="{{ route('communications.send') }}">
                            @csrf
                            <input type="hidden" name="recipient_type" value="individual">
                            <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                            <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                            <button type="submit"
                                    onclick="setTimeout(()=>{this.textContent='Sending…';this.style.opacity='0.55';},10)"
                                    class="db-remind-btn">
                                Remind
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

    </div>
</div>

{{-- ── Income vs Expenses chart ── --}}
@php
    $maxValue      = collect($chartData)->max(fn($d) => max($d['income'], $d['expenses']));
    $maxValue      = $maxValue > 0 ? $maxValue * 1.15 : 1;
    $totalIncome   = collect($chartData)->sum('income');
    $totalExpenses = collect($chartData)->sum('expenses');
    $totalProfit   = $totalIncome - $totalExpenses;
@endphp

<div class="db-section">
    <div class="db-chart-card">
        <div class="db-chart-head">
            <div>
                <p class="db-card-title" style="margin:0 0 2px">Income vs Expenses</p>
                <p style="font-size:12px;color:var(--mute);margin:0">Last 6 months</p>
            </div>
            <div class="db-chart-totals">
                <div class="db-chart-total">
                    <div class="db-chart-total-label">Income</div>
                    <div class="db-chart-total-val" style="color:#15803d">{{ currency($totalIncome) }}</div>
                </div>
                <div class="db-chart-total">
                    <div class="db-chart-total-label">Expenses</div>
                    <div class="db-chart-total-val" style="color:var(--red)">{{ currency($totalExpenses) }}</div>
                </div>
                <div class="db-chart-total">
                    <div class="db-chart-total-label">Net</div>
                    <div class="db-chart-total-val" style="color:{{ $totalProfit >= 0 ? '#15803d' : 'var(--red)' }}">
                        {{ $totalProfit < 0 ? '-' : '' }}{{ currency(abs($totalProfit)) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="db-chart-body">
            <div class="db-chart-legend">
                <div class="db-chart-legend-item">
                    <div class="db-chart-legend-dot" style="background:#22c55e"></div> Income
                </div>
                <div class="db-chart-legend-item">
                    <div class="db-chart-legend-dot" style="background:#fca5a5"></div> Expenses
                </div>
            </div>
            <div style="overflow-x:auto">
                <div style="min-width:280px">
                    <div class="db-bars">
                        @foreach($chartData as $data)
                        @php
                            $incH = $maxValue > 0 ? ($data['income']   / $maxValue) * 148 : 0;
                            $expH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 148 : 0;
                        @endphp
                        <div class="db-bar-group">
                            <div class="db-bar-pair">
                                <div class="db-bar" style="background:#22c55e;height:{{ $incH }}px;min-height:{{ $data['income']>0?3:0 }}px"></div>
                                <div class="db-bar" style="background:#fca5a5;height:{{ $expH }}px;min-height:{{ $data['expenses']>0?3:0 }}px"></div>
                            </div>
                            <div class="db-bar-label">{{ $data['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Properties overview ── --}}
<div class="db-section" style="margin-top:16px">
    <p class="db-section-label">Your portfolio</p>
    <div class="db-card">
        <div class="db-card-head">
            <p class="db-card-title">Properties</p>
            <a href="{{ route('properties.index') }}" class="db-card-link">Manage</a>
        </div>
        @if($propertiesOverview->isEmpty())
            <div class="db-empty">No properties added yet</div>
        @else
            @foreach($propertiesOverview as $prop)
            @php
                $rate      = $prop->units_count > 0 ? round(($prop->occupied_count/$prop->units_count)*100) : 0;
                $rateColor = $rate >= 80 ? '#15803d' : '#b45309';
                $rateBg    = $rate >= 80 ? '#dcfce7' : '#fef3c7';
                $gR = 15; $gCx = $gCy = 19;
                $gCirc = 2 * M_PI * $gR;
                $gDash = ($rate / 100) * $gCirc;
            @endphp
            <a href="{{ route('properties.show',$prop) }}" class="db-prop-row">
                <svg width="40" height="40" viewBox="0 0 38 38" style="flex-shrink:0" aria-label="{{ $rate }}% occupied" role="img">
                    <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="var(--line)" stroke-width="4"/>
                    @if($rate > 0)
                    <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="{{ $rateColor }}" stroke-width="4"
                            stroke-dasharray="{{ $gDash }} {{ $gCirc - $gDash }}"
                            stroke-linecap="round"
                            transform="rotate(-90 {{ $gCx }} {{ $gCy }})"/>
                    @endif
                    <text x="{{ $gCx }}" y="{{ $gCy + 3 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="8" font-weight="700" fill="{{ $rateColor }}">{{ $rate }}%</text>
                </svg>
                <div style="flex:1;min-width:0">
                    <div class="db-prop-name">{{ $prop->name }}</div>
                    <div class="db-prop-sub">{{ $prop->occupied_count }} of {{ $prop->units_count }} units occupied</div>
                </div>
                <span class="db-prop-badge" style="background:{{ $rateBg }};color:{{ $rateColor }}">
                    {{ $prop->units_count - $prop->occupied_count }} vacant
                </span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--mute-2)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            @endforeach
        @endif
    </div>
</div>

</div>
</x-layouts.app>