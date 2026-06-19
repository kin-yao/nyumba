<x-layouts.app>
<style>
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
    --red:       #b91c1c;
    --red-soft:  #fee2e2;
}

.dash-wrap {
    padding: clamp(16px,4vw,32px);
    padding-bottom: 64px;
    background: var(--paper);
    min-height: 100vh;
}

/* ── Hero ── */
.dash-hero {
    position: relative;
    background: var(--green-deep);
    border-radius: 12px;
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

/* ── Secondary stat row ── */
.dash-secondary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}
.dash-sec-card {
    background: var(--card);
    border-radius: 10px;
    padding: 16px 20px;
    border: 1px solid var(--line);
}

/* ── Tertiary KPI row ── */
.dash-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.kpi-card {
    background: var(--card);
    border-radius: 8px;
    padding: 14px 16px;
    border: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: border-color .15s;
}
.kpi-card:hover { border-color: var(--green); }
.kpi-icon {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background: var(--paper);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Two col ── */
.dash-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

/* ── Card base ── */
.dash-card {
    background: var(--card);
    border-radius: 10px;
    border: 1px solid var(--line);
    overflow: hidden;
}
.dash-card-head {
    padding: 13px 18px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dash-card-title {
    font-weight: 500;
    font-size: 13px;
    color: var(--ink);
}
.dash-view-link {
    font-size: 12px;
    font-weight: 500;
    color: var(--green);
    text-decoration: none;
}

/* ── Chart ── */
.dash-chart {
    background: var(--card);
    border-radius: 10px;
    border: 1px solid var(--line);
    padding: 20px;
    margin-bottom: 12px;
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
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .dash-kpi { grid-template-columns: repeat(2,1fr); }
    .dash-hero-donut { display: none; }
}
@media (max-width: 700px) {
    .dash-2col { grid-template-columns: 1fr; }
    .dash-secondary { grid-template-columns: 1fr; }
    .dash-hero-content { padding: 20px 18px; }
}
@media (max-width: 480px) {
    .dash-kpi { grid-template-columns: repeat(2,1fr); }
}
</style>

<div class="dash-wrap">

@php $account = auth()->user()->account; @endphp

{{-- ── Expired banner ── --}}
@if($account && $account->isExpired())
<div style="background:var(--card);border:2px solid var(--red);border-radius:10px;padding:24px;margin-bottom:16px;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">🔒</div>
    <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:6px">
        @if($account->plan==='explore') Your free trial has ended
        @else Your {{ ucfirst($account->plan) }} subscription has expired
        @endif
    </div>
    <div style="font-size:13px;color:var(--mute);margin-bottom:18px;max-width:420px;margin-left:auto;margin-right:auto;line-height:1.6">
        @if($account->plan==='explore')
            Trial ended {{ $account->trial_ends_at?->format('d M Y') }}.
        @else
            Expired {{ $account->plan_expires_at?->format('d M Y') }}.
        @endif
        Your data is safe. Upgrade to continue.
    </div>
    <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
       target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#25D366;color:#fff;border-radius:7px;font-size:14px;font-weight:500;text-decoration:none">
        WhatsApp us to upgrade
    </a>
</div>
@endif

{{-- ── Flash messages ── --}}
@if(session('success'))
<div style="background:var(--green-soft);border:1px solid #a7d7c5;border-radius:8px;padding:10px 16px;margin-bottom:10px;font-size:13px;color:var(--green)">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:var(--red-soft);border:1px solid #fca5a5;border-radius:8px;padding:10px 16px;margin-bottom:10px;font-size:13px;color:var(--red)">
    {{ session('error') }}
</div>
@endif

{{-- ── Alerts ── --}}
@if($urgentMaintenance > 0)
<div class="dash-alert" style="background:var(--red-soft);border:1px solid #fca5a5;color:var(--red)">
    <span>{{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request',$urgentMaintenance) }}</span>
    <a href="{{ route('maintenance.index') }}" style="color:var(--red);font-weight:500;text-decoration:none;font-size:12px;background:rgba(185,28,28,.1);padding:4px 10px;border-radius:6px">View →</a>
</div>
@endif
@if($overdueCount > 0)
<div class="dash-alert" style="background:#fef3c7;border:1px solid #fcd34d;color:#78350f">
    <span>{{ $overdueCount }} overdue {{ Str::plural('invoice',$overdueCount) }}</span>
    <a href="{{ route('invoices.index') }}" style="color:#78350f;font-weight:500;text-decoration:none;font-size:12px;background:rgba(120,53,15,.1);padding:4px 10px;border-radius:6px">View →</a>
</div>
@endif

{{-- ── Hero: pie chart + collected/outstanding ── --}}
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
    <div class="dash-hero-shards">
        <svg width="100%" height="100%" viewBox="0 0 1200 260" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <polygon points="-72,0 792,0 480,260 -72,260" fill="#ffffff" opacity="0.04"/>
            <polygon points="96,0 756,0 360,260 -72,260" fill="#ffffff" opacity="0.05"/>
        </svg>
    </div>

    <div class="dash-hero-content">
        <div style="display:grid;grid-template-columns:1fr auto;gap:28px;align-items:center;padding-bottom:24px">

            <div>
                <div style="font-size:11px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;color:rgba(244,242,236,.5);margin-bottom:6px">
                    {{ \Carbon\Carbon::createFromDate($year,$month,1)->format('F Y') }}
                </div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(22px,4vw,28px);color:#fff;line-height:1.15;margin-bottom:14px">
                    Hello {{ explode(' ',auth()->user()->name)[0] }}
                </div>

                <div style="display:flex;gap:24px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:11px;color:rgba(244,242,236,.5);margin-bottom:4px">Collected this month</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:clamp(22px,3.5vw,28px);color:#4ade80">{{ currency($collectedThisMonth) }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:rgba(244,242,236,.5);margin-bottom:4px">Outstanding</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:clamp(22px,3.5vw,28px);color:#fca5a5">{{ currency($outstandingThisMonth) }}</div>
                    </div>
                </div>
            </div>

            {{-- Pie chart with legend --}}
            <div class="dash-hero-donut" style="flex-shrink:0;text-align:center">
                <svg width="108" height="108" viewBox="0 0 104 104">
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="rgba(244,242,236,.12)" stroke-width="16"/>
                    @if($outstandingPct > 0)
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="#f87171" stroke-width="16"
                            stroke-dasharray="{{ $dOut }} {{ $dColl }}"
                            stroke-dashoffset="{{ -$dColl }}"
                            transform="rotate(-90 {{ $dCx }} {{ $dCy }})"/>
                    @endif
                    @if($collectedPct > 0)
                    <circle cx="{{ $dCx }}" cy="{{ $dCy }}" r="{{ $dR }}" fill="none" stroke="#4ade80" stroke-width="16"
                            stroke-dasharray="{{ $dColl }} {{ $dOut }}"
                            transform="rotate(-90 {{ $dCx }} {{ $dCy }})"/>
                    @endif
                    <text x="{{ $dCx }}" y="{{ $dCy + 6 }}" text-anchor="middle" font-family="DM Serif Display,serif" font-size="20" font-weight="600" fill="#fff">{{ $collectionRate }}%</text>
                </svg>
                <div style="display:flex;gap:12px;justify-content:center;margin-top:8px">
                    <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:rgba(244,242,236,.6)">
                        <div style="width:8px;height:8px;border-radius:50%;background:#4ade80"></div> Collected
                    </div>
                    <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:rgba(244,242,236,.6)">
                        <div style="width:8px;height:8px;border-radius:50%;background:#f87171"></div> Owed
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Secondary: Occupancy + Net profit ── --}}
<div class="dash-secondary">
    <div class="dash-sec-card">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:11px;color:var(--mute);margin-bottom:4px">Portfolio occupancy</div>
                <div style="font-family:'DM Serif Display',serif;font-size:24px;color:{{ $occupancyRate >= 80 ? 'var(--green)' : 'var(--gold)' }}">{{ $occupancyRate }}%</div>
            </div>
            <div style="text-align:right;font-size:12px;color:var(--mute)">
                {{ $occupiedUnits }} occupied<br>{{ $vacantUnits }} vacant
            </div>
        </div>
    </div>
    <div class="dash-sec-card">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:11px;color:var(--mute);margin-bottom:4px">Net profit this month</div>
                <div style="font-family:'DM Serif Display',serif;font-size:24px;color:{{ $netProfitThisMonth >= 0 ? 'var(--green)' : 'var(--red)' }}">
                    {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
                </div>
            </div>
            <div style="text-align:right;font-size:12px;color:var(--mute)">
                {{ $netProfitThisMonth >= 0 ? 'Profitable' : 'Loss' }}
            </div>
        </div>
    </div>
</div>

{{-- ── Tertiary KPI row ── --}}
<div class="dash-kpi">
    @foreach([
        ['Properties',       $totalProperties, '#1a6b52',  route('properties.index'),  'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z'],
        ['Tenants',          $totalTenants,    '#1a6b52',  route('tenants.index'),     'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z'],
        ['Maintenance',      $openMaintenance, $openMaintenance>0?'#d97706':'#1a6b52', route('maintenance.index'), 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z'],
        ['Overdue',          $overdueCount,    $overdueCount>0?'#b91c1c':'#1a6b52', route('invoices.index'), 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8'],
    ] as [$label, $value, $color, $href, $iconPath])
    <a href="{{ $href }}" class="kpi-card">
        <div class="kpi-icon" style="color:{{ $color }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconPath }}"/></svg>
        </div>
        <div>
            <div style="font-weight:600;font-size:18px;color:{{ $color }};line-height:1">{{ $value }}</div>
            <div style="font-size:11px;color:var(--mute);margin-top:2px">{{ $label }}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- ── Recent payments + Outstanding balances ── --}}
<div class="dash-2col">

    <div class="dash-card">
        <div class="dash-card-head">
            <div class="dash-card-title">Recent payments</div>
            <a href="{{ route('payments.index') }}" class="dash-view-link">View all</a>
        </div>
        @if($recentPayments->isEmpty())
            <div style="padding:32px;text-align:center;color:var(--mute);font-size:13px">No payments yet</div>
        @else
            @foreach($recentPayments as $pmt)
            <div style="padding:11px 18px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="display:flex;align-items:center;gap:9px;min-width:0">
                    <div style="width:30px;height:30px;border-radius:50%;background:var(--green-soft);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--green);flex-shrink:0">
                        {{ $pmt->tenant ? strtoupper(substr($pmt->tenant->first_name,0,1).substr($pmt->tenant->last_name,0,1)) : '?' }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $pmt->tenant?->full_name ?? 'Unknown' }}</div>
                        <div style="font-size:11px;color:var(--mute)">{{ $pmt->payment_date->format('d M') }} &middot; {{ strtoupper($pmt->method) }}</div>
                    </div>
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--green);flex-shrink:0">{{ currency($pmt->amount) }}</div>
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
            <div style="padding:32px;text-align:center;color:var(--mute);font-size:13px">All tenants up to date</div>
        @else
            @foreach($tenantsWithBalance as $item)
            <div style="padding:11px 18px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="display:flex;align-items:center;gap:9px;flex:1;min-width:0">
                    <div style="width:30px;height:30px;border-radius:50%;background:var(--red-soft);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--red);flex-shrink:0">
                        {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item['tenant']->full_name }}</div>
                        <div style="font-size:11px;color:var(--mute)">{{ $item['unit']->name }} &middot; {{ $item['property']->name }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    <div style="font-size:13px;font-weight:600;color:var(--red)">{{ currency($item['balance']) }}</div>
                    <form method="POST" action="{{ route('communications.send') }}">
                        @csrf
                        <input type="hidden" name="recipient_type" value="individual">
                        <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                        <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                        <button type="submit"
                                onclick="setTimeout(()=>{this.textContent='Sending…';this.style.opacity='0.6';},10)"
                                style="font-size:11px;font-weight:500;padding:4px 10px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:6px;cursor:pointer;white-space:nowrap">
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
    $totalIncome   = collect($chartData)->sum('income');
    $totalExpenses = collect($chartData)->sum('expenses');
@endphp

<div class="dash-chart">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
        <div class="dash-card-title">Income vs Expenses — last 6 months</div>
        <div style="display:flex;gap:16px">
            <div style="font-size:12px;color:var(--mute)">Income <strong style="color:var(--green)">{{ currency($totalIncome) }}</strong></div>
            <div style="font-size:12px;color:var(--mute)">Expenses <strong style="color:var(--red)">{{ currency($totalExpenses) }}</strong></div>
        </div>
    </div>

    <div style="overflow-x:auto">
        <div style="min-width:300px">
            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px;height:170px;padding-bottom:30px;position:relative">
                @foreach($chartData as $data)
                @php
                    $incH = $maxValue > 0 ? ($data['income']   / $maxValue) * 140 : 0;
                    $expH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 140 : 0;
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center">
                    <div style="display:flex;gap:3px;align-items:flex-end;width:100%;justify-content:center;margin-bottom:6px">
                        <div style="flex:1;max-width:20px;background:var(--green);border-radius:3px 3px 0 0;height:{{ $incH }}px;min-height:{{ $data['income']>0?2:0 }}px" title="{{ currency($data['income']) }}"></div>
                        <div style="flex:1;max-width:20px;background:#fca5a5;border-radius:3px 3px 0 0;height:{{ $expH }}px;min-height:{{ $data['expenses']>0?2:0 }}px" title="{{ currency($data['expenses']) }}"></div>
                    </div>
                    <div style="font-size:10px;color:var(--mute)">{{ $data['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Properties overview with gauge charts ── --}}
<div class="dash-card">
    <div class="dash-card-head">
        <div class="dash-card-title">Properties</div>
        <a href="{{ route('properties.index') }}" class="dash-view-link">Manage</a>
    </div>
    @if($propertiesOverview->isEmpty())
        <div style="padding:32px;text-align:center;color:var(--mute);font-size:13px">No properties yet</div>
    @else
        @foreach($propertiesOverview as $prop)
        @php
            $rate      = $prop->units_count > 0 ? round(($prop->occupied_count/$prop->units_count)*100) : 0;
            $rateColor = $rate >= 80 ? '#1a6b52' : '#c2924f';
            $gR    = 16; $gCx = $gCy = 20;
            $gCirc = 2 * M_PI * $gR;
            $gDash = ($rate / 100) * $gCirc;
        @endphp
        <a href="{{ route('properties.show',$prop) }}"
           style="display:flex;align-items:center;gap:14px;padding:12px 18px;border-bottom:1px solid var(--line);text-decoration:none;color:inherit">
            <svg width="44" height="44" viewBox="0 0 40 40" style="flex-shrink:0">
                <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="var(--paper-2)" stroke-width="5"/>
                <circle cx="{{ $gCx }}" cy="{{ $gCy }}" r="{{ $gR }}" fill="none" stroke="{{ $rateColor }}" stroke-width="5"
                        stroke-dasharray="{{ $gDash }} {{ $gCirc - $gDash }}"
                        stroke-linecap="round"
                        transform="rotate(-90 {{ $gCx }} {{ $gCy }})"/>
                <text x="{{ $gCx }}" y="{{ $gCy + 3 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" font-weight="600" fill="var(--ink)">{{ $rate }}%</text>
            </svg>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:500">{{ $prop->name }}</div>
                <div style="font-size:11px;color:var(--mute)">{{ $prop->occupied_count }} of {{ $prop->units_count }} units occupied</div>
            </div>
        </a>
        @endforeach
    @endif
</div>

</div>
</x-layouts.app>