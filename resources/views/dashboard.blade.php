<x-layouts.app>
<style>
@import url('https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600&f[]=satoshi@400,500,700&f[]=cabinet-grotesk@500,700&display=swap');

:root {
    --ink:       #14110f;
    --ink-2:     #2b2722;
    --paper:     #f4f2ec;
    --paper-2:   #ebe7dd;
    --card:      #fff;
    --green:     #1a6b52;
    --green-deep:#0e3f30;
    --green-soft:#e4efe9;
    --gold:      #c2924f;
    --line:      #ddd6c9;
    --mute:      #857f73;
    --shadow:    0 24px 60px -28px rgba(20,17,15,.32);
    --red:       #b91c1c;
    --red-soft:  #fee2e2;
}

.dash-wrap {
    padding: clamp(16px,4vw,32px);
    padding-bottom: 64px;
    background: var(--paper);
    min-height: 100vh;
    font-family: 'Satoshi', 'DM Sans', sans-serif;
}

/* ── Hero ── */
.dash-hero {
    position: relative;
    background: var(--ink);
    overflow: hidden;
    margin-bottom: 16px;
}
.dash-hero-shards {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.dash-hero-content {
    position: relative;
    z-index: 2;
    padding: 28px 28px 0;
}
.dash-hero-top {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 28px;
}
.dash-hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid rgba(244,242,236,.1);
    margin: 0 -28px;
}
.dash-hero-stat {
    padding: 18px 24px;
    border-right: 1px solid rgba(244,242,236,.08);
}
.dash-hero-stat:last-child { border-right: none; }
.dash-eyebrow {
    font-family: 'Cabinet Grotesk', sans-serif;
    font-weight: 700;
    font-size: 9px;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: rgba(244,242,236,.35);
    margin-bottom: 5px;
}

/* ── KPI cards ── */
.dash-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.kpi-card {
    background: var(--card);
    border-radius: 8px;
    padding: 18px 20px;
    border: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    display: block;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 1px 0 rgba(20,17,15,.04);
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

/* ── Two col ── */
.dash-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 16px;
}

/* ── Card base ── */
.dash-card {
    background: var(--card);
    border-radius: 8px;
    border: 1px solid var(--line);
    overflow: hidden;
    box-shadow: 0 1px 0 rgba(20,17,15,.04);
}
.dash-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dash-card-title {
    font-family: 'Satoshi', sans-serif;
    font-weight: 700;
    font-size: 13px;
    color: var(--ink);
}
.dash-card-sub {
    font-size: 11px;
    color: var(--mute);
    margin-top: 2px;
}
.dash-view-link {
    font-family: 'Cabinet Grotesk', sans-serif;
    font-weight: 700;
    font-size: 10px;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--green);
    text-decoration: none;
    background: var(--green-soft);
    padding: 4px 10px;
    border-radius: 4px;
}

/* ── Chart ── */
.dash-chart {
    background: var(--card);
    border-radius: 8px;
    border: 1px solid var(--line);
    padding: 22px 24px;
    margin-bottom: 16px;
    box-shadow: 0 1px 0 rgba(20,17,15,.04);
}

/* ── Alert ── */
.dash-alert {
    border-radius: 6px;
    padding: 11px 16px;
    margin-bottom: 10px;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .dash-kpi { grid-template-columns: repeat(2,1fr); }
    .dash-hero-stats { grid-template-columns: repeat(2,1fr); }
    .dash-hero-stat:nth-child(2) { border-right: none; }
    .dash-hero-stat:nth-child(3) { border-top: 1px solid rgba(244,242,236,.08); }
    .dash-hero-stat:nth-child(4) { border-top: 1px solid rgba(244,242,236,.08); border-right: none; }
    .dash-hero-top { grid-template-columns: 1fr; }
    .dash-hero-donut { display: none; }
}
@media (max-width: 700px) {
    .dash-2col { grid-template-columns: 1fr; }
    .dash-hero-content { padding: 20px 18px 0; }
    .dash-hero-stats { margin: 0 -18px; }
    .dash-hero-stat { padding: 14px 16px; }
    .dash-occ-row { grid-template-columns: 1fr !important; }
}
@media (max-width: 480px) {
    .dash-kpi { grid-template-columns: repeat(2,1fr); }
}
</style>

<div class="dash-wrap">

@php $account = auth()->user()->account; @endphp

{{-- ── Expired banner ── --}}
@if($account && $account->isExpired())
<div style="background:var(--card);border:2px solid var(--red);border-radius:8px;padding:28px 24px;margin-bottom:20px;text-align:center">
    <div style="font-size:32px;margin-bottom:10px">🔒</div>
    <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:22px;letter-spacing:-.02em;margin-bottom:8px">
        @if($account->plan==='explore') Your free trial has ended
        @else Your {{ ucfirst($account->plan) }} subscription has expired
        @endif
    </div>
    <div style="font-size:13px;color:var(--mute);margin-bottom:22px;max-width:460px;margin-left:auto;margin-right:auto;line-height:1.7">
        @if($account->plan==='explore')
            Your 30-day free trial ended on {{ $account->trial_ends_at?->format('d M Y') }}.
        @else
            Your subscription expired on {{ $account->plan_expires_at?->format('d M Y') }}.
        @endif
        Upgrade to keep managing your properties.
        <br><strong style="color:var(--ink)">Your data is safe and fully retained.</strong>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px;text-align:left;max-width:520px;margin-left:auto;margin-right:auto">
        @foreach([['Starter','2,300',20,80,false],['Growth','4,600',50,200,true],['Pro','7,500',100,400,false]] as [$pn,$pp,$pu,$ps,$pop])
        <div style="background:var(--paper);border-radius:6px;border:{{ $pop?'2px solid var(--green)':'1px solid var(--line)' }};padding:14px;position:relative">
            @if($pop)<div style="position:absolute;top:-9px;left:50%;transform:translateX(-50%);background:var(--green);color:#fff;font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;padding:2px 10px;border-radius:999px;letter-spacing:.1em;white-space:nowrap">POPULAR</div>@endif
            <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;color:var(--mute);text-transform:uppercase;letter-spacing:.12em;margin-bottom:5px">{{ $pn }}</div>
            <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:20px;letter-spacing:-.02em">KES {{ $pp }}<span style="font-size:11px;font-family:'Satoshi',sans-serif;color:var(--mute)">/mo</span></div>
            <div style="font-size:11px;color:var(--mute);margin-top:5px">{{ $pu }} units &middot; {{ $ps }} SMS/mo</div>
        </div>
        @endforeach
    </div>
    <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
       target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#25D366;color:#fff;border-radius:4px;font-family:'Satoshi',sans-serif;font-size:14px;font-weight:700;text-decoration:none">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Contact us on WhatsApp to upgrade
    </a>
</div>
@endif

{{-- ── Alerts ── --}}
@if($urgentMaintenance > 0)
<div class="dash-alert" style="background:var(--red-soft);border:1px solid #fca5a5;color:var(--red)">
    <span>⚠ {{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request',$urgentMaintenance) }} need attention</span>
    <a href="{{ route('maintenance.index') }}" style="color:var(--red);font-weight:700;text-decoration:none;font-size:11px;font-family:'Cabinet Grotesk',sans-serif;letter-spacing:.08em;text-transform:uppercase;background:rgba(185,28,28,.1);padding:4px 10px;border-radius:4px">View →</a>
</div>
@endif
@if($overdueCount > 0)
<div class="dash-alert" style="background:#fef3c7;border:1px solid #fcd34d;color:#78350f">
    <span>{{ $overdueCount }} overdue {{ Str::plural('invoice',$overdueCount) }} require follow up</span>
    <a href="{{ route('invoices.index') }}" style="color:#78350f;font-weight:700;text-decoration:none;font-size:11px;font-family:'Cabinet Grotesk',sans-serif;letter-spacing:.08em;text-transform:uppercase;background:rgba(120,53,15,.1);padding:4px 10px;border-radius:4px">View →</a>
</div>
@endif

{{-- ── Hero ── --}}
@php
    $totalExpected = $expectedThisMonth > 0 ? $expectedThisMonth : 1;
    $collectedPct  = min(100, ($collectedThisMonth / $totalExpected) * 100);
    $outstandingPct= 100 - $collectedPct;
    $dR = 44; $dCx = $dCy = 52;
    $dCirc = 2 * M_PI * $dR;
    $dColl = ($collectedPct / 100) * $dCirc;
    $dOut  = $dCirc - $dColl;
@endphp

<div class="dash-hero">
    {{-- Geometric shards (matching landing page) --}}
    <div class="dash-hero-shards">
        <svg width="100%" height="100%" viewBox="0 0 1200 280" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <polygon points="-72,0 792,0 480,280 -72,280" fill="#1a6b52" opacity="0.22"/>
            <polygon points="96,0 756,0 360,280 -72,280" fill="#0e3f30" opacity="0.28"/>
            <polygon points="360,0 864,0 480,280 144,280" fill="#c2924f" opacity="0.06"/>
        </svg>
    </div>

    <div class="dash-hero-content">
        <div class="dash-hero-top">
            <div>
                <div style="font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:rgba(244,242,236,.35);margin-bottom:10px">
                    {{ now()->format('l, d F Y') }} &middot; {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }} Overview
                </div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(24px,5vw,36px);letter-spacing:-.025em;line-height:.96;color:#f4f2ec;margin-bottom:10px">
                    Hello {{ explode(' ',auth()->user()->name)[0] }},<br>
                    <em style="font-style:italic;font-weight:400;color:#9ed8c2">Karibu nyumbani!</em>
                </div>
                <div style="font-size:12px;color:rgba(244,242,236,.4);font-family:'Cabinet Grotesk',sans-serif;letter-spacing:.04em">
                    {{ $account->name }} &middot;
                    <span style="color:var(--gold);font-weight:700">{{ ucfirst($account->plan) }} plan</span>
                </div>
            </div>

            {{-- Donut chart --}}
            <div class="dash-hero-donut" style="flex-shrink:0;text-align:center">
                <svg width="104" height="104" viewBox="0 0 104 104">
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="rgba(244,242,236,.08)" stroke-width="10"/>
                    @if($outstandingPct > 0)
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="#ef4444" stroke-width="10" opacity="0.55"
                            stroke-dasharray="{{ $dOut }} {{ $dColl }}"
                            stroke-dashoffset="{{ -$dColl }}"
                            transform="rotate(-90 {{ $dCx }} {{ $dCy }})"/>
                    @endif
                    @if($collectedPct > 0)
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="#4ade80" stroke-width="10"
                            stroke-dasharray="{{ $dColl }} {{ $dOut }}"
                            transform="rotate(-90 {{ $dCx }} {{ $dCy }})"/>
                    @endif
                    <text x="{{ $dCx }}" y="{{ $dCy - 5 }}" text-anchor="middle" font-family="Clash Display,sans-serif" font-size="16" font-weight="600" fill="#f4f2ec">{{ $collectionRate }}%</text>
                    <text x="{{ $dCx }}" y="{{ $dCy + 10 }}" text-anchor="middle" font-family="Cabinet Grotesk,sans-serif" font-size="8" fill="rgba(244,242,236,.35)" letter-spacing="1">COLLECTED</text>
                </svg>
                <div style="display:flex;gap:10px;justify-content:center;margin-top:6px">
                    <div style="display:flex;align-items:center;gap:4px;font-family:'Cabinet Grotesk',sans-serif;font-size:9px;letter-spacing:.06em;color:rgba(244,242,236,.35)">
                        <div style="width:6px;height:6px;border-radius:50%;background:#4ade80"></div> PAID
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;font-family:'Cabinet Grotesk',sans-serif;font-size:9px;letter-spacing:.06em;color:rgba(244,242,236,.35)">
                        <div style="width:6px;height:6px;border-radius:50%;background:#ef4444;opacity:.55"></div> OWED
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats strip --}}
        <div class="dash-hero-stats">
            <div class="dash-hero-stat">
                <div class="dash-eyebrow">Collected</div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(16px,2.5vw,22px);letter-spacing:-.02em;color:#4ade80;line-height:1.1">{{ currency($collectedThisMonth) }}</div>
                <div style="font-size:10px;color:rgba(244,242,236,.3);margin-top:4px;font-family:'Cabinet Grotesk',sans-serif">{{ number_format($collectedPct,1) }}% of expected</div>
            </div>
            <div class="dash-hero-stat">
                <div class="dash-eyebrow">Outstanding</div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(16px,2.5vw,22px);letter-spacing:-.02em;color:#fca5a5;line-height:1.1">{{ currency($outstandingThisMonth) }}</div>
                <div style="font-size:10px;color:rgba(244,242,236,.3);margin-top:4px;font-family:'Cabinet Grotesk',sans-serif">{{ number_format($outstandingPct,1) }}% of expected</div>
            </div>
            <div class="dash-hero-stat">
                <div class="dash-eyebrow">Expected</div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(16px,2.5vw,22px);letter-spacing:-.02em;color:#f4f2ec;line-height:1.1">{{ currency($expectedThisMonth) }}</div>
                <div style="font-size:10px;color:rgba(244,242,236,.3);margin-top:4px;font-family:'Cabinet Grotesk',sans-serif">Total rent roll</div>
            </div>
            <div class="dash-hero-stat">
                <div class="dash-eyebrow">Net profit</div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(16px,2.5vw,22px);letter-spacing:-.02em;color:{{ $netProfitThisMonth >= 0 ? '#4ade80' : '#fca5a5' }};line-height:1.1">
                    {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
                </div>
                <div style="font-size:10px;color:rgba(244,242,236,.3);margin-top:4px;font-family:'Cabinet Grotesk',sans-serif">
                    {{ $netProfitThisMonth >= 0 ? 'Profitable month ↑' : 'Net loss ↓' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── KPI cards ── --}}
<div class="dash-kpi">
    @foreach([
        ['Properties',       $totalProperties, '#1a6b52',  null,         route('properties.index'),  'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z'],
        ['Active tenants',   $totalTenants,    '#1a6b52',  null,         route('tenants.index'),     'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z'],
        ['Open maintenance', $openMaintenance, $openMaintenance>0?'#d97706':'#1a6b52', $openMaintenance>0?'#fef3c7':null, route('maintenance.index'), 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z'],
        ['Overdue invoices', $overdueCount,    $overdueCount>0?'#b91c1c':'#1a6b52', $overdueCount>0?'#fee2e2':null, route('invoices.index'), 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8'],
    ] as [$label, $value, $color, $bg, $href, $iconPath])
    <a href="{{ $href }}" class="kpi-card" style="{{ $bg ? 'background:'.$bg.';border-color:transparent;' : '' }}">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
            <div style="width:32px;height:32px;border-radius:6px;background:{{ $bg ? 'rgba(0,0,0,0.07)' : 'var(--paper)' }};display:flex;align-items:center;justify-content:center;color:{{ $color }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconPath }}"/></svg>
            </div>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="2" stroke-linecap="round">
                <path d="M7 17L17 7M17 7H7M17 7v10"/>
            </svg>
        </div>
        <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:clamp(24px,3vw,30px);letter-spacing:-.025em;color:{{ $color }};line-height:1">{{ $value }}</div>
        <div style="font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:{{ $bg ? $color : 'var(--mute)' }};margin-top:6px">{{ $label }}</div>
    </a>
    @endforeach
</div>

{{-- ── Recent payments + Outstanding balances ── --}}
<div class="dash-2col">

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Recent payments</div>
                <div class="dash-card-sub">Latest transactions recorded</div>
            </div>
            <a href="{{ route('payments.index') }}" class="dash-view-link">View all</a>
        </div>
        @if($recentPayments->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">
                <div style="font-size:26px;margin-bottom:8px">💳</div>No payments recorded yet
            </div>
        @else
            @foreach($recentPayments as $pmt)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="display:flex;align-items:center;gap:10px;min-width:0">
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--green-soft);display:flex;align-items:center;justify-content:center;font-family:'Clash Display',sans-serif;font-size:12px;font-weight:600;color:var(--green);flex-shrink:0">
                        {{ $pmt->tenant ? strtoupper(substr($pmt->tenant->first_name,0,1).substr($pmt->tenant->last_name,0,1)) : '?' }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--ink)">{{ $pmt->tenant?->full_name ?? 'Unknown' }}</div>
                        <div style="font-size:11px;color:var(--mute);margin-top:1px;display:flex;align-items:center;gap:6px">
                            {{ $pmt->payment_date->format('d M Y') }}
                            <span style="background:var(--paper);border:1px solid var(--line);padding:1px 6px;border-radius:3px;font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.08em">{{ strtoupper($pmt->method) }}</span>
                        </div>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:15px;letter-spacing:-.01em;color:var(--green)">{{ currency($pmt->amount) }}</div>
                    <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mute);margin-top:2px">{{ ucfirst($pmt->payment_type) }}</div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Outstanding balances</div>
                <div class="dash-card-sub">Tenants requiring follow-up</div>
            </div>
            <a href="{{ route('reports.outstanding') }}" class="dash-view-link">Full report</a>
        </div>
        @if($tenantsWithBalance->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">
                <div style="font-size:26px;margin-bottom:8px">✅</div>All tenants are up to date
            </div>
        @else
            @foreach($tenantsWithBalance as $item)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--red-soft);display:flex;align-items:center;justify-content:center;font-family:'Clash Display',sans-serif;font-size:12px;font-weight:600;color:var(--red);flex-shrink:0">
                        {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--ink)">{{ $item['tenant']->full_name }}</div>
                        <div style="font-size:11px;color:var(--mute);margin-top:1px">Unit {{ $item['unit']->name }} &middot; {{ $item['property']->name }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    <div style="text-align:right">
                        <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:15px;letter-spacing:-.01em;color:var(--red)">{{ currency($item['balance']) }}</div>
                        <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mute);margin-top:2px">overdue</div>
                    </div>
                    <form method="POST" action="{{ route('communications.send') }}">
                        @csrf
                        <input type="hidden" name="recipient_type" value="individual">
                        <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                        <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                        <button type="submit" style="font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 10px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:4px;cursor:pointer;white-space:nowrap">
                            Remind
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── Income vs Expenses chart ── --}}
@php
    $maxValue      = collect($chartData)->max(fn($d) => max($d['income'], $d['expenses']));
    $maxValue      = $maxValue > 0 ? $maxValue * 1.15 : 1;
    $currSymbol    = currency_symbol();
    $totalIncome   = collect($chartData)->sum('income');
    $totalExpenses = collect($chartData)->sum('expenses');
    $totalProfit   = collect($chartData)->sum('profit');
@endphp

<div class="dash-chart">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:14px">
        <div>
            <div style="font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--green);margin-bottom:6px">Performance</div>
            <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:18px;letter-spacing:-.02em;color:var(--ink)">Income vs Expenses</div>
            <div style="font-size:12px;color:var(--mute);margin-top:2px">Last 6 months</div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
            @foreach([['Income',$totalIncome,'var(--green)'],['Expenses',$totalExpenses,'var(--red)'],['Net',abs($totalProfit),$totalProfit>=0?'var(--green)':'var(--red)']] as [$lbl,$val,$col])
            <div style="text-align:right">
                <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--mute);margin-bottom:3px">{{ $lbl }}</div>
                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:17px;letter-spacing:-.02em;color:{{ $col }}">{{ currency($val) }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="display:flex;gap:14px;margin-bottom:16px">
        <div style="display:flex;align-items:center;gap:5px;font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mute)">
            <div style="width:10px;height:10px;border-radius:2px;background:var(--green)"></div> Income
        </div>
        <div style="display:flex;align-items:center;gap:5px;font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mute)">
            <div style="width:10px;height:10px;border-radius:2px;background:#fca5a5"></div> Expenses
        </div>
    </div>

    <div style="overflow-x:auto">
        <div style="min-width:300px">
            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px;height:190px;padding-bottom:34px;position:relative">
                @foreach([0,25,50,75,100] as $pct)
                <div style="position:absolute;left:0;right:0;bottom:{{ $pct*1.56+34 }}px;border-top:1px dashed var(--line);z-index:0">
                    <span style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;color:var(--mute);position:absolute;left:0;top:-7px">
                        {{ $pct > 0 ? $currSymbol.' '.number_format($maxValue*$pct/100/1000,0).'k' : '0' }}
                    </span>
                </div>
                @endforeach
                @foreach($chartData as $data)
                @php
                    $incH = $maxValue > 0 ? ($data['income']   / $maxValue) * 156 : 0;
                    $expH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 156 : 0;
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1">
                    <div style="display:flex;gap:3px;align-items:flex-end;width:100%;justify-content:center;margin-bottom:8px">
                        <div style="flex:1;max-width:22px">
                            <div style="width:100%;background:var(--green);border-radius:3px 3px 0 0;height:{{ $incH }}px;min-height:{{ $data['income']>0?2:0 }}px" title="{{ currency($data['income']) }}"></div>
                        </div>
                        <div style="flex:1;max-width:22px">
                            <div style="width:100%;background:#fca5a5;border-radius:3px 3px 0 0;height:{{ $expH }}px;min-height:{{ $data['expenses']>0?2:0 }}px" title="{{ currency($data['expenses']) }}"></div>
                        </div>
                    </div>
                    <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mute);text-align:center">{{ $data['label'] }}</div>
                    <div style="font-family:'Clash Display',sans-serif;font-size:10px;font-weight:600;margin-top:2px;color:{{ $data['profit']>=0?'var(--green)':'var(--red)' }}">
                        {{ $data['profit']>=0?'+':'' }}{{ number_format($data['profit']/1000,0) }}k
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Occupancy + Properties ── --}}
<div style="display:grid;grid-template-columns:210px 1fr;gap:14px;align-items:start" class="dash-occ-row">

    <div class="dash-card" style="overflow:visible">
        <div style="padding:16px 18px 0">
            <div style="font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--green);margin-bottom:4px">Portfolio</div>
            <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:16px;letter-spacing:-.02em;color:var(--ink);margin-bottom:16px">Occupancy</div>
        </div>
        @php
            $oR = 50; $oCx = $oCy = 62;
            $oCirc = 2 * M_PI * $oR;
            $oDash = ($occupancyRate / 100) * $oCirc;
        @endphp
        <div style="display:flex;justify-content:center;padding:0 18px">
            <svg width="124" height="124" viewBox="0 0 124 124">
                <circle cx="{{ $oCx }}" cy="{{ $oCy }}" r="{{ $oR }}" fill="none" stroke="var(--paper-2)" stroke-width="12"/>
                @if($occupancyRate > 0)
                <circle cx="{{ $oCx }}" cy="{{ $oCy }}" r="{{ $oR }}" fill="none"
                        stroke="{{ $occupancyRate >= 80 ? 'var(--green)' : 'var(--gold)' }}" stroke-width="12"
                        stroke-dasharray="{{ $oDash }} {{ $oCirc - $oDash }}"
                        stroke-linecap="round"
                        transform="rotate(-90 {{ $oCx }} {{ $oCy }})"/>
                @endif
                <text x="{{ $oCx }}" y="{{ $oCy - 4 }}" text-anchor="middle" font-family="Clash Display,sans-serif" font-size="22" font-weight="600" fill="#14110f">{{ $occupancyRate }}%</text>
                <text x="{{ $oCx }}" y="{{ $oCy + 13 }}" text-anchor="middle" font-family="Cabinet Grotesk,sans-serif" font-size="8" fill="#857f73" letter-spacing="1.5">OCCUPIED</text>
            </svg>
        </div>
        <div style="padding:12px 18px 18px;display:grid;gap:7px">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--green-soft);border-radius:6px">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--green)"></div>
                    <span style="font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--green)">Occupied</span>
                </div>
                <span style="font-family:'Clash Display',sans-serif;font-size:16px;font-weight:600;color:var(--green)">{{ $occupiedUnits }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:var(--paper);border-radius:6px;border:1px solid var(--line)">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--mute)"></div>
                    <span style="font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mute)">Vacant</span>
                </div>
                <span style="font-family:'Clash Display',sans-serif;font-size:16px;font-weight:600;color:var(--ink-2)">{{ $vacantUnits }}</span>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div style="font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--green);margin-bottom:4px">Portfolio</div>
                <div class="dash-card-title">Properties</div>
            </div>
            <a href="{{ route('properties.index') }}" class="dash-view-link">Manage</a>
        </div>
        @if($propertiesOverview->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--mute);font-size:13px">
                <div style="font-size:26px;margin-bottom:8px">🏘</div>No properties added yet
            </div>
        @else
            @foreach($propertiesOverview as $prop)
            @php
                $rate   = $prop->units_count > 0 ? round(($prop->occupied_count/$prop->units_count)*100) : 0;
                $vacant = $prop->units_count - $prop->occupied_count;
                $rateColor = $rate >= 80 ? 'var(--green)' : 'var(--gold)';
            @endphp
            <a href="{{ route('properties.show',$prop) }}"
               style="display:block;padding:14px 20px;border-bottom:1px solid var(--line);text-decoration:none;color:inherit;transition:background .15s"
               onmouseover="this.style.background='var(--paper)'" onmouseout="this.style.background=''">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:38px;height:38px;border-radius:8px;background:var(--green-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:17px">
                        {{ $prop->type==='commercial'?'🏢':($prop->type==='mixed'?'🏙':'🏘') }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--ink)">{{ $prop->name }}</div>
                                <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mute);margin-top:2px">{{ $prop->area ?? $prop->county ?? '' }} &middot; {{ $prop->units_count }} units</div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;margin-left:12px">
                                <div style="font-family:'Clash Display',sans-serif;font-weight:600;font-size:15px;letter-spacing:-.01em;color:{{ $rateColor }}">{{ $rate }}%</div>
                                <div style="font-family:'Cabinet Grotesk',sans-serif;font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mute)">{{ $prop->occupied_count }} occ &middot; {{ $vacant }} vac</div>
                            </div>
                        </div>
                        <div style="height:4px;background:var(--paper-2);border-radius:2px;overflow:hidden">
                            <div style="height:100%;background:{{ $rateColor }};border-radius:2px;width:{{ $rate }}%"></div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        @endif
    </div>
</div>

</div>

<style>
@media (max-width:700px) {
    .dash-occ-row { grid-template-columns: 1fr !important; }
}
</style>

</x-layouts.app>