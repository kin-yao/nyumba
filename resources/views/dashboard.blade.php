<x-layouts.app>
<style>
:root {
    --ink:        #111827;
    --mute:       #6b7280;
    --paper:      #f9fafb;
    --card:       #ffffff;
    --line:       #e5e7eb;
    --green:      #1a6b52;
    --green-deep: #0e3f30;
    --green-soft: #ecfdf5;
    --green-mid:  #d1fae5;
    --gold:       #c2924f;
    --gold-soft:  #fef3c7;
    --red:        #dc2626;
    --red-soft:   #fef2f2;
    --blue-soft:  #eff6ff;
    --blue:       #2563eb;
    --purple-soft:#f5f3ff;
    --purple:     #7c3aed;
}

* { box-sizing: border-box; }

.dash-wrap {
    padding: 24px clamp(16px,3vw,28px) 64px;
    background: var(--paper);
    min-height: 100vh;
}

/* ── Hero ── */
.dash-hero-wrap {
    position: relative;
    background: var(--green-deep);
    overflow: hidden;
    margin: -24px clamp(-16px,-3vw,-28px) 20px;
    padding: 28px clamp(16px,3vw,28px) 0;
}
.dash-hero-shards {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.dash-hero-inner {
    position: relative;
    z-index: 2;
}

/* Pie + stat cards layout */
.dash-hero-body {
    display: grid;
    grid-template-columns: 1fr 220px;
    gap: 24px;
    align-items: center;
    margin-bottom: 24px;
}

/* Stat cards stacked on the right */
.hero-stat-cards {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.hero-stat-card {
    border-radius: 10px;
    padding: 14px 16px;
}

/* Quick stats */
.dash-quick-stats {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    border-top: 1px solid rgba(255,255,255,.08);
}
.quick-stat {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    border-right: 1px solid rgba(255,255,255,.08);
    transition: background .15s;
}
.quick-stat:last-child { border-right: none; }
.quick-stat:hover { background: rgba(255,255,255,.06); }
.quick-stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── KPI grid ── */
.dash-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 0;
    margin-bottom: 20px;
    border: 1px solid var(--line);
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.kpi-card {
    background: var(--card);
    padding: 20px 22px;
    border-right: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    display: block;
    transition: background .15s;
}
.kpi-card:last-child { border-right: none; }
.kpi-card:hover { background: #f9fafb; }
.kpi-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

/* ── Two col ── */
.dash-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

/* ── Cards ── */
.dash-card {
    background: var(--card);
    border-radius: 12px;
    border: 1px solid var(--line);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dash-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dash-card-title { font-weight: 600; font-size: 14px; color: var(--ink); }
.dash-view-link {
    font-size: 12px;
    font-weight: 500;
    color: var(--green);
    text-decoration: none;
    background: var(--green-soft);
    padding: 3px 10px;
    border-radius: 20px;
}

/* ── Chart ── */
.dash-chart {
    background: var(--card);
    border-radius: 12px;
    border: 1px solid var(--line);
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}

/* ── Alert ── */
.dash-alert {
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 10px;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    font-weight: 500;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .dash-kpi-grid { grid-template-columns: repeat(2,1fr); }
    .kpi-card:nth-child(2) { border-right: none; }
    .kpi-card:nth-child(3) { border-top: 1px solid var(--line); }
    .kpi-card:nth-child(4) { border-top: 1px solid var(--line); border-right: none; }
}

@media (max-width: 800px) {
    .dash-2col { grid-template-columns: 1fr; }
    /* On tablet: stat cards go below pie */
    .dash-hero-body {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .hero-stat-cards {
        flex-direction: row;
        gap: 8px;
    }
    .hero-stat-card { flex: 1; }
    .dash-quick-stats { grid-template-columns: 1fr 1fr 1fr; }
}

@media (max-width: 640px) {
    .dash-kpi-grid { grid-template-columns: repeat(2,1fr); }
    .dash-quick-stats { grid-template-columns: 1fr 1fr 1fr; }
    .quick-stat { padding: 12px 10px; gap: 8px; }
}

@media (max-width: 420px) {
    .hero-stat-cards { flex-direction: column; }
    .dash-quick-stats { grid-template-columns: 1fr; }
    .quick-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); }
    .quick-stat:last-child { border-bottom: none; }
}
</style>

<div class="dash-wrap">

@php $account = auth()->user()->account; @endphp

{{-- ── Expired banner ── --}}
@if($account && $account->isExpired())
<div style="background:var(--card);border:2px solid var(--red);border-radius:12px;padding:28px;margin-bottom:20px;text-align:center">
    <div style="font-size:32px;margin-bottom:10px">🔒</div>
    <div style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:8px">
        @if($account->plan==='explore') Free trial ended
        @else {{ ucfirst($account->plan) }} plan expired
        @endif
    </div>
    <div style="font-size:13px;color:var(--mute);margin-bottom:20px">Your data is safe. Upgrade to restore access.</div>
    <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
       target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#25D366;color:#fff;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none">
        WhatsApp us to upgrade
    </a>
</div>
@endif

{{-- ── Flash messages ── --}}
@if(session('success'))
<div style="background:var(--green-soft);border:1px solid var(--green-mid);border-radius:8px;padding:10px 16px;margin-bottom:12px;font-size:13px;color:var(--green);font-weight:500">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:var(--red-soft);border:1px solid #fecaca;border-radius:8px;padding:10px 16px;margin-bottom:12px;font-size:13px;color:var(--red);font-weight:500">
    {{ session('error') }}
</div>
@endif

{{-- ── Alerts ── --}}
@if($urgentMaintenance > 0)
<div class="dash-alert" style="background:#fef2f2;border:1px solid #fecaca;color:var(--red)">
    <span>⚠ {{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request',$urgentMaintenance) }}</span>
    <a href="{{ route('maintenance.index') }}" style="color:var(--red);font-weight:600;text-decoration:none;font-size:12px;background:#fee2e2;padding:4px 12px;border-radius:20px">View →</a>
</div>
@endif
@if($overdueCount > 0)
<div class="dash-alert" style="background:var(--gold-soft);border:1px solid #fde68a;color:#92400e">
    <span>{{ $overdueCount }} overdue {{ Str::plural('invoice',$overdueCount) }}</span>
    <a href="{{ route('invoices.index') }}" style="color:#92400e;font-weight:600;text-decoration:none;font-size:12px;background:#fde68a;padding:4px 12px;border-radius:20px">View →</a>
</div>
@endif

{{-- ── Hero ── --}}
@php
    $totalExpected  = $expectedThisMonth > 0 ? $expectedThisMonth : 1;
    $collectedPct   = min(100, ($collectedThisMonth / $totalExpected) * 100);
    $outstandingPct = max(0, 100 - $collectedPct);

    // If nothing expected yet, show full grey ring
    $showEmpty = $expectedThisMonth == 0;

    $r = 80; $cx = 100; $cy = 100;
    $circ = 2 * M_PI * $r;

    $collectedArc   = ($collectedPct  / 100) * $circ;
    $collectedGap   = $circ - $collectedArc;
    $outstandingArc = ($outstandingPct / 100) * $circ;
    $outstandingGap = $circ - $outstandingArc;
    // Outstanding starts where collected ends
    $outstandingOffset = -$collectedArc;
@endphp

<div class="dash-hero-wrap">
    <div class="dash-hero-shards">
        <svg width="100%" height="100%" viewBox="0 0 1200 500" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <polygon points="-72,0 792,0 480,500 -72,500" fill="#ffffff" opacity="0.03"/>
            <polygon points="96,0 756,0 360,500 -72,500" fill="#ffffff" opacity="0.04"/>
            <polygon points="600,0 1200,0 1200,500 800,500" fill="#ffffff" opacity="0.02"/>
            <circle cx="960" cy="80" r="220" fill="#ffffff" opacity="0.02"/>
        </svg>
    </div>

    <div class="dash-hero-inner">

        {{-- Greeting --}}
        <div style="margin-bottom:20px">
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,24px);color:#fff">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ',auth()->user()->name)[0] }}
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,.4);margin-top:3px">
                {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }}
            </div>
        </div>

        {{-- Pie + stat cards ── --}}
        <div class="dash-hero-body">

            {{-- Pie chart ── --}}
            <div style="display:flex;flex-direction:column;align-items:center">
                <svg width="200" height="200" viewBox="0 0 200 200">
                    {{-- Background track --}}
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                            fill="none"
                            stroke="rgba(255,255,255,.08)"
                            stroke-width="28"/>

                    @if($showEmpty)
                        {{-- No data — full grey ring --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                fill="none"
                                stroke="rgba(255,255,255,.15)"
                                stroke-width="28"/>
                    @else
                        @if($outstandingPct > 0)
                        {{-- Outstanding — red, drawn first (behind) --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                fill="none"
                                stroke="#ef4444"
                                stroke-width="28"
                                stroke-dasharray="{{ $outstandingArc }} {{ $outstandingGap }}"
                                stroke-dashoffset="{{ $outstandingOffset }}"
                                transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                        @endif

                        @if($collectedPct > 0)
                        {{-- Collected — green, drawn on top --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                fill="none"
                                stroke="#22c55e"
                                stroke-width="28"
                                stroke-dasharray="{{ $collectedArc }} {{ $collectedGap }}"
                                transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                        @endif
                    @endif

                    {{-- Centre rate --}}
                    <text x="{{ $cx }}" y="{{ $cy - 10 }}"
                          text-anchor="middle"
                          font-family="DM Serif Display,serif"
                          font-size="30"
                          font-weight="700"
                          fill="#fff">{{ $collectionRate }}%</text>
                    <text x="{{ $cx }}" y="{{ $cy + 12 }}"
                          text-anchor="middle"
                          font-family="DM Sans,sans-serif"
                          font-size="10"
                          fill="rgba(255,255,255,.5)"
                          letter-spacing="2">COLLECTED</text>
                </svg>

                {{-- Legend ── --}}
                <div style="display:flex;gap:16px;margin-top:8px">
                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:rgba(255,255,255,.6)">
                        <div style="width:9px;height:9px;border-radius:50%;background:#22c55e"></div> Collected
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:rgba(255,255,255,.6)">
                        <div style="width:9px;height:9px;border-radius:50%;background:#ef4444"></div> Outstanding
                    </div>
                </div>
            </div>

            {{-- Stat cards ── --}}
            <div class="hero-stat-cards">
                {{-- Collected — white card --}}
                <div class="hero-stat-card" style="background:#fff">
                    <div style="font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#22c55e;margin-bottom:6px">Collected</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,22px);color:#111827;line-height:1">{{ currency($collectedThisMonth) }}</div>
                </div>

                {{-- Outstanding — red card --}}
                <div class="hero-stat-card" style="background:#ef4444">
                    <div style="font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:6px">Outstanding</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,22px);color:#fff;line-height:1">{{ currency($outstandingThisMonth) }}</div>
                </div>

                {{-- Expected — blue card --}}
                <div class="hero-stat-card" style="background:#2563eb">
                    <div style="font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:6px">Expected</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,22px);color:#fff;line-height:1">{{ currency($expectedThisMonth) }}</div>
                </div>
            </div>
        </div>

        {{-- Quick stats ── --}}
        <div class="dash-quick-stats">
            <a href="{{ route('maintenance.index') }}" class="quick-stat">
                <div class="quick-stat-icon" style="background:{{ $openMaintenance > 0 ? 'rgba(251,146,60,.2)' : 'rgba(34,197,94,.15)' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $openMaintenance > 0 ? '#fb923c' : '#22c55e' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:{{ $openMaintenance > 0 ? '#fb923c' : '#fff' }}">{{ $openMaintenance }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.45)">Open maintenance</div>
                </div>
            </a>
            <a href="{{ route('invoices.index') }}" class="quick-stat">
                <div class="quick-stat-icon" style="background:{{ $overdueCount > 0 ? 'rgba(239,68,68,.2)' : 'rgba(34,197,94,.15)' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $overdueCount > 0 ? '#ef4444' : '#22c55e' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:{{ $overdueCount > 0 ? '#ef4444' : '#fff' }}">{{ $overdueCount }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.45)">Overdue invoices</div>
                </div>
            </a>
            <a href="{{ route('tenants.index') }}" class="quick-stat">
                <div class="quick-stat-icon" style="background:rgba(167,139,250,.18)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:#fff">{{ $occupiedUnits }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.45)">Occupied units</div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ── KPI grid ── --}}
<div class="dash-kpi-grid">
    <a href="{{ route('properties.index') }}" class="kpi-card">
        <div class="kpi-icon-wrap" style="background:var(--green-soft)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1a6b52" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:30px;color:var(--ink);line-height:1;margin-bottom:4px">{{ $totalProperties }}</div>
        <div style="font-size:12px;font-weight:500;color:var(--mute)">Properties</div>
    </a>
    <a href="{{ route('tenants.index') }}" class="kpi-card">
        <div class="kpi-icon-wrap" style="background:var(--blue-soft)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:30px;color:var(--ink);line-height:1;margin-bottom:4px">{{ $totalTenants }}</div>
        <div style="font-size:12px;font-weight:500;color:var(--mute)">Active tenants</div>
    </a>
    <a href="{{ route('properties.index') }}" class="kpi-card">
        <div class="kpi-icon-wrap" style="background:{{ $occupancyRate >= 80 ? 'var(--green-soft)' : 'var(--gold-soft)' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $occupancyRate >= 80 ? 'var(--green)' : 'var(--gold)' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:30px;color:{{ $occupancyRate >= 80 ? 'var(--green)' : 'var(--gold)' }};line-height:1;margin-bottom:4px">{{ $occupancyRate }}%</div>
        <div style="font-size:12px;font-weight:500;color:var(--mute)">Occupancy &middot; {{ $vacantUnits }} vacant</div>
    </a>
    <a href="{{ route('reports.index') }}" class="kpi-card">
        <div class="kpi-icon-wrap" style="background:{{ $netProfitThisMonth >= 0 ? 'var(--green-soft)' : 'var(--red-soft)' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $netProfitThisMonth >= 0 ? 'var(--green)' : 'var(--red)' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:30px;color:{{ $netProfitThisMonth >= 0 ? 'var(--green)' : 'var(--red)' }};line-height:1;margin-bottom:4px">
            {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
        </div>
        <div style="font-size:12px;font-weight:500;color:var(--mute)">Net profit</div>
    </a>
</div>

{{-- ── Recent payments + Outstanding balances ── --}}
<div class="dash-2col">
    <div class="dash-card">
        <div class="dash-card-head">
            <div class="dash-card-title">Recent payments</div>
            <a href="{{ route('payments.index') }}" class="dash-view-link">View all</a>
        </div>
        @if($recentPayments->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">
                <div style="font-size:28px;margin-bottom:8px">💳</div>No payments yet
            </div>
        @else
            @foreach($recentPayments as $pmt)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:10px">
                <div style="display:flex;align-items:center;gap:10px;min-width:0">
                    <div style="width:34px;height:34px;border-radius:8px;background:var(--green-soft);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--green);flex-shrink:0">
                        {{ $pmt->tenant ? strtoupper(substr($pmt->tenant->first_name,0,1).substr($pmt->tenant->last_name,0,1)) : '?' }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--ink)">{{ $pmt->tenant?->full_name ?? 'Unknown' }}</div>
                        <div style="font-size:11px;color:var(--mute);margin-top:1px">{{ $pmt->payment_date->format('d M') }} &middot; {{ strtoupper($pmt->method) }}</div>
                    </div>
                </div>
                <div style="font-size:13px;font-weight:700;color:#16a34a;flex-shrink:0">{{ currency($pmt->amount) }}</div>
            </div>
            @endforeach
        @endif
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div class="dash-card-title">Outstanding balances</div>
            <a href="{{ route('reports.outstanding') }}" class="dash-view-link">Full report</a>
        </div>
        @if($tenantsWithBalance->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">
                <div style="font-size:28px;margin-bottom:8px">✅</div>All tenants up to date
            </div>
        @else
            @foreach($tenantsWithBalance as $item)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:10px">
                <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                    <div style="width:34px;height:34px;border-radius:8px;background:var(--red-soft);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--red);flex-shrink:0">
                        {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--ink)">{{ $item['tenant']->full_name }}</div>
                        <div style="font-size:11px;color:var(--mute);margin-top:1px">{{ $item['unit']->name }} &middot; {{ $item['property']->name }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    <div style="font-size:13px;font-weight:700;color:var(--red)">{{ currency($item['balance']) }}</div>
                    <form method="POST" action="{{ route('communications.send') }}">
                        @csrf
                        <input type="hidden" name="recipient_type" value="individual">
                        <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                        <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                        <button type="submit"
                                onclick="setTimeout(()=>{this.textContent='Sending…';this.style.opacity='0.6';},10)"
                                style="font-size:11px;font-weight:600;padding:4px 12px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:20px;cursor:pointer;white-space:nowrap">
                            Remind
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── Income vs Expenses ── --}}
@php
    $maxValue      = collect($chartData)->max(fn($d) => max($d['income'], $d['expenses']));
    $maxValue      = $maxValue > 0 ? $maxValue * 1.15 : 1;
    $totalIncome   = collect($chartData)->sum('income');
    $totalExpenses = collect($chartData)->sum('expenses');
    $totalProfit   = $totalIncome - $totalExpenses;
@endphp

<div class="dash-chart">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div>
            <div class="dash-card-title">Income vs Expenses</div>
            <div style="font-size:12px;color:var(--mute);margin-top:2px">Last 6 months</div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
            <div style="text-align:right">
                <div style="font-size:11px;color:var(--mute);margin-bottom:2px">Income</div>
                <div style="font-size:14px;font-weight:700;color:#16a34a">{{ currency($totalIncome) }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-size:11px;color:var(--mute);margin-bottom:2px">Expenses</div>
                <div style="font-size:14px;font-weight:700;color:var(--red)">{{ currency($totalExpenses) }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-size:11px;color:var(--mute);margin-bottom:2px">Net</div>
                <div style="font-size:14px;font-weight:700;color:{{ $totalProfit >= 0 ? '#16a34a' : 'var(--red)' }}">{{ currency(abs($totalProfit)) }}</div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:14px;margin-bottom:16px">
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--mute)">
            <div style="width:10px;height:10px;border-radius:3px;background:#22c55e"></div> Income
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--mute)">
            <div style="width:10px;height:10px;border-radius:3px;background:#fca5a5"></div> Expenses
        </div>
    </div>

    <div style="overflow-x:auto">
        <div style="min-width:300px">
            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px;height:180px;padding-bottom:32px">
                @foreach($chartData as $data)
                @php
                    $incH = $maxValue > 0 ? ($data['income']   / $maxValue) * 148 : 0;
                    $expH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 148 : 0;
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center">
                    <div style="display:flex;gap:4px;align-items:flex-end;width:100%;justify-content:center;margin-bottom:8px">
                        <div style="flex:1;max-width:22px;background:#22c55e;border-radius:3px 3px 0 0;height:{{ $incH }}px;min-height:{{ $data['income']>0?3:0 }}px"></div>
                        <div style="flex:1;max-width:22px;background:#fca5a5;border-radius:3px 3px 0 0;height:{{ $expH }}px;min-height:{{ $data['expenses']>0?3:0 }}px"></div>
                    </div>
                    <div style="font-size:10px;font-weight:500;color:var(--mute)">{{ $data['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Properties ── --}}
<div class="dash-card">
    <div class="dash-card-head">
        <div class="dash-card-title">Properties</div>
        <a href="{{ route('properties.index') }}" class="dash-view-link">Manage</a>
    </div>
    @if($propertiesOverview->isEmpty())
        <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">No properties yet</div>
    @else
        @foreach($propertiesOverview as $prop)
        @php
            $rate      = $prop->units_count > 0 ? round(($prop->occupied_count/$prop->units_count)*100) : 0;
            $rateColor = $rate >= 80 ? '#16a34a' : '#f59e0b';
            $rateBg    = $rate >= 80 ? '#d1fae5' : '#fef3c7';
            $gR = 16; $gCx = $gCy = 20;
            $gCirc = 2 * M_PI * $gR;
            $gDash = ($rate / 100) * $gCirc;
        @endphp
        <a href="{{ route('properties.show',$prop) }}"
           style="display:flex;align-items:center;gap:16px;padding:13px 20px;border-bottom:1px solid var(--line);text-decoration:none;color:inherit;transition:background .15s"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
            <svg width="44" height="44" viewBox="0 0 40 40" style="flex-shrink:0">
                <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="#e5e7eb" stroke-width="5"/>
                @if($rate > 0)
                <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="{{ $rateColor }}" stroke-width="5"
                        stroke-dasharray="{{ $gDash }} {{ $gCirc - $gDash }}"
                        stroke-linecap="round"
                        transform="rotate(-90 {{ $gCx }} {{ $gCy }})"/>
                @endif
                <text x="{{ $gCx }}" y="{{ $gCy + 3 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" font-weight="700" fill="{{ $rateColor }}">{{ $rate }}%</text>
            </svg>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--ink)">{{ $prop->name }}</div>
                <div style="font-size:12px;color:var(--mute);margin-top:2px">{{ $prop->occupied_count }} of {{ $prop->units_count }} units occupied</div>
            </div>
            <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;background:{{ $rateBg }};color:{{ $rateColor }};flex-shrink:0">
                {{ $prop->units_count - $prop->occupied_count }} vacant
            </span>
        </a>
        @endforeach
    @endif
</div>

</div>
</x-layouts.app>