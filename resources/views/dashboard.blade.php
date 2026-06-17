<x-layouts.app>
<style>
/* ── Base ── */
.dash-wrap {
    padding: clamp(16px, 4vw, 32px);
    padding-bottom: 48px;
    background: #f0f2f0;
    min-height: 100vh;
}

/* ── Card base ── */
.card {
    background: #fff;
    border-radius: 12px;
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}
.card-dark {
    background: #0d2818;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* ── Grids ── */
.dash-row1 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1.6fr;
    gap: 14px;
    margin-bottom: 14px;
}
.dash-row2 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
.dash-row3 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
.dash-props {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 0;
}
.dash-summary {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

/* ── Quick action btns ── */
.qa-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity .15s, transform .15s;
    white-space: nowrap;
}
.qa-btn:hover { opacity: .85; transform: translateY(-1px); }
.qa-btn-primary   { background: #1a6b52; color: #fff; }
.qa-btn-secondary { background: #fff; color: #111110; border: 1px solid rgba(0,0,0,0.1); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

/* ── Badge ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red   { background: #fee2e2; color: #b91c1c; }
.badge-amber { background: #fef3c7; color: #92400e; }

/* ── Avatar ── */
.avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700;
    flex-shrink: 0;
}

/* ── Responsive ── */
@media (max-width: 1100px) {
    .dash-row1 { grid-template-columns: 1fr 1fr; }
    .dash-row1 .kpi-collection { grid-column: 1 / -1; }
    .dash-row2 { grid-template-columns: 1fr; }
    .dash-props { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 700px) {
    .dash-row1 { grid-template-columns: 1fr 1fr; }
    .dash-row2 { grid-template-columns: 1fr; }
    .dash-row3 { grid-template-columns: 1fr; }
    .dash-props { grid-template-columns: 1fr; }
    .dash-summary { grid-template-columns: 1fr; gap: 10px; }
    .dash-actions { justify-content: center; }
    .dash-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .collection-inner { flex-direction: row !important; align-items: center !important; gap: 14px !important; }
    .collection-inner svg { width: 100px !important; height: 100px !important; flex-shrink: 0; }
}
@media (max-width: 420px) {
    .dash-row1 { grid-template-columns: 1fr; }
    .dash-wrap { padding: 12px; }
}
</style>

<div class="dash-wrap">

    {{-- ── Expired banner ── --}}
    @php $account = auth()->user()->account; @endphp
    @if($account && $account->isExpired())
        <div style="background:#fff;border:2px solid #b91c1c;border-radius:12px;padding:28px 32px;margin-bottom:20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
            <div style="font-size:36px;margin-bottom:10px">🔒</div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:8px">
                @if($account->plan === 'explore') Your free trial has ended
                @else Your {{ ucfirst($account->plan) }} subscription has expired
                @endif
            </div>
            <div style="font-size:13px;color:#8a8880;margin-bottom:20px;max-width:480px;margin-left:auto;margin-right:auto;line-height:1.7">
                @if($account->plan === 'explore')
                    Your 30-day free trial ended on {{ $account->trial_ends_at?->format('d M Y') }}. Upgrade to keep managing your properties.
                @else
                    Your subscription expired on {{ $account->plan_expires_at?->format('d M Y') }}. Renew to restore all features.
                @endif
                <br><strong style="color:#111110">Your data is safe and fully retained.</strong>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;text-align:left;max-width:520px;margin-left:auto;margin-right:auto">
                @foreach([
                    ['name'=>'Starter','price'=>'2,300','units'=>20,'sms'=>80, 'popular'=>false],
                    ['name'=>'Growth', 'price'=>'4,600','units'=>50,'sms'=>200,'popular'=>true],
                    ['name'=>'Pro',    'price'=>'7,500','units'=>100,'sms'=>400,'popular'=>false],
                ] as $plan)
                    <div style="background:#f9fafb;border-radius:10px;border:{{ $plan['popular'] ? '2px solid #1a6b52' : '1px solid rgba(0,0,0,0.07)' }};padding:14px;position:relative">
                        @if($plan['popular'])
                            <div style="position:absolute;top:-9px;left:50%;transform:translateX(-50%);background:#1a6b52;color:#fff;font-size:9px;font-weight:700;padding:2px 9px;border-radius:10px;white-space:nowrap">POPULAR</div>
                        @endif
                        <div style="font-size:11px;font-weight:600;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ $plan['name'] }}</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:18px;margin-bottom:3px">KES {{ $plan['price'] }}<span style="font-size:11px;font-family:'DM Sans',sans-serif;color:#8a8880">/mo</span></div>
                        <div style="font-size:11px;color:#8a8880">{{ $plan['units'] }} units · {{ $plan['sms'] }} SMS/mo</div>
                    </div>
                @endforeach
            </div>
            <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
               target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#25D366;color:#fff;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Contact us on WhatsApp to upgrade
            </a>
            <div style="font-size:11px;color:#8a8880;margin-top:8px">Pay 6 months, get 1 free · Pay 12 months, get 2 free</div>
        </div>
    @endif

    {{-- ── Header ── --}}
    @php $credits = auth()->user()->account->sms_credits ?? 0; @endphp
    <div class="dash-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,4vw,26px);line-height:1.2;color:#111110">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }} Report
            </div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ now()->format('l, d F Y') }}
                &nbsp;&middot;&nbsp;
                Hello, <strong style="color:#1a6b52">{{ explode(' ', auth()->user()->name)[0] }}</strong>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            {{-- SMS credits badge --}}
            <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:#fff;border-radius:8px;font-size:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                    <path d="M2 2h10a1 1 0 011 1v6a1 1 0 01-1 1H4L1 12V3a1 1 0 011-1z" stroke="{{ $credits <= 20 ? '#b91c1c' : '#1a6b52' }}" stroke-width="1.4" stroke-linejoin="round"/>
                </svg>
                <span style="color:#8a8880"><strong style="color:{{ $credits <= 20 ? '#b91c1c' : '#111110' }}">{{ $credits }}</strong> SMS credits</span>
                @if($credits <= 20)
                    <span class="badge badge-red">Low</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Alerts ── --}}
    @if($urgentMaintenance > 0)
        <div style="background:#fff;border-left:3px solid #f97316;border-radius:8px;padding:10px 16px;margin-bottom:10px;font-size:13px;color:#9a3412;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;box-shadow:0 1px 2px rgba(0,0,0,0.05)">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M8 1L1 14h14L8 1z" stroke="#f97316" stroke-width="1.4" stroke-linejoin="round"/><path d="M8 6v4M8 11.5v.5" stroke="#f97316" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request', $urgentMaintenance) }} need attention
            </div>
            <a href="{{ route('maintenance.index') }}" style="color:#f97316;font-weight:600;font-size:12px;text-decoration:none">View →</a>
        </div>
    @endif
    @if($overdueCount > 0)
        <div style="background:#fff;border-left:3px solid #d97706;border-radius:8px;padding:10px 16px;margin-bottom:10px;font-size:13px;color:#92400e;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;box-shadow:0 1px 2px rgba(0,0,0,0.05)">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#d97706" stroke-width="1.4"/><path d="M8 5v4M8 10.5v.5" stroke="#d97706" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $overdueCount }} overdue {{ Str::plural('invoice', $overdueCount) }} require follow up
            </div>
            <a href="{{ route('invoices.index') }}" style="color:#d97706;font-weight:600;font-size:12px;text-decoration:none">View →</a>
        </div>
    @endif

    {{-- ── Quick actions ── --}}
    <div class="dash-actions" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px">
        <a href="{{ route('payments.create') }}" class="qa-btn qa-btn-primary">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="8" rx="1" stroke="currentColor" stroke-width="1.3"/><path d="M1 6h12" stroke="currentColor" stroke-width="1.3"/></svg>
            Record payment
        </a>
        <a href="{{ route('tenants.create') }}" class="qa-btn qa-btn-secondary">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="4.5" r="2.5" stroke="currentColor" stroke-width="1.3"/><path d="M1.5 12c0-2.5 2.5-4 5.5-4s5.5 1.5 5.5 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
            Add tenant
        </a>
        <a href="{{ route('invoices.index') }}" class="qa-btn qa-btn-secondary">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M3 1h8v12l-2-1.5L7 13l-2-1.5L3 13V1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
            Invoices
        </a>
        <a href="{{ route('communications.index') }}" class="qa-btn qa-btn-secondary">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M2 2h10a1 1 0 011 1v6a1 1 0 01-1 1H4L1 12V3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
            Send message
        </a>
        <a href="{{ route('reports.index') }}" class="qa-btn qa-btn-secondary">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M2 12V7l3-3 3 2 3-4 1 1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Reports
        </a>
    </div>

    {{-- ── Row 1: 3 KPI cards + Collection donut ── --}}
    @php
        $totalExpected  = $expectedThisMonth > 0 ? $expectedThisMonth : 1;
        $collectedPct   = min(100, ($collectedThisMonth / $totalExpected) * 100);
        $outstandingPct = 100 - $collectedPct;
        $radius         = 54;
        $cx = $cy       = 72;
        $circumference  = 2 * M_PI * $radius;
        $collectedDash  = ($collectedPct / 100) * $circumference;
    @endphp

    <div class="dash-row1">

        {{-- Net profit --}}
        <div class="card" style="padding:20px 22px;position:relative;overflow:hidden">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <div style="width:36px;height:36px;border-radius:8px;background:#e6f2ed;display:flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 13V6l5-4 5 4v7H2z" stroke="#1a6b52" stroke-width="1.3" stroke-linejoin="round"/><rect x="5.5" y="9" width="3" height="4" rx=".5" stroke="#1a6b52" stroke-width="1.2"/></svg>
                </div>
                <span class="badge {{ $netProfitThisMonth >= 0 ? 'badge-green' : 'badge-red' }}">
                    {{ $netProfitThisMonth >= 0 ? '↑' : '↓' }} {{ $netProfitThisMonth >= 0 ? 'Profit' : 'Loss' }}
                </span>
            </div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:5px">Net profit this month</div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,24px);color:#111110;margin-bottom:4px">
                {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
            </div>
            <div style="font-size:11px;color:#8a8880">
                Income {{ currency($paymentsThisMonth) }} · Exp {{ currency($expensesThisMonth) }}
            </div>
        </div>

        {{-- Occupancy --}}
        <div class="card" style="padding:20px 22px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <div style="width:36px;height:36px;border-radius:8px;background:{{ $occupancyRate >= 80 ? '#e6f2ed' : '#fee2e2' }};display:flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }}" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }}" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }}" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }}" stroke-width="1.3" stroke-dasharray="2 1.5"/></svg>
                </div>
                <span class="badge {{ $occupancyRate >= 80 ? 'badge-green' : 'badge-red' }}">
                    {{ $occupancyRate >= 80 ? '✓ Healthy' : '↓ Low' }}
                </span>
            </div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:5px">Occupancy rate</div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,24px);color:#111110;margin-bottom:8px">{{ $occupancyRate }}%</div>
            <div style="height:5px;background:#f0f2f0;border-radius:3px;overflow:hidden">
                <div style="height:100%;background:{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }};width:{{ $occupancyRate }}%"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#8a8880;margin-top:5px">
                <span>{{ $occupiedUnits }} occupied</span>
                <span>{{ $vacantUnits }} vacant</span>
            </div>
        </div>

        {{-- KPI strip --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            @foreach([
                ['Properties',    $totalProperties, '#e6f2ed','#1a6b52', route('properties.index'), null],
                ['Active tenants',$totalTenants,    '#e6f2ed','#1a6b52', route('tenants.index'),    null],
                ['Maintenance',   $openMaintenance, $openMaintenance > 0 ? '#fef3c7' : '#f0f2f0', $openMaintenance > 0 ? '#92400e' : '#8a8880', route('maintenance.index'), $openMaintenance > 0 ? 'badge-amber' : null],
                ['Overdue',       $overdueCount,    $overdueCount > 0    ? '#fee2e2' : '#f0f2f0', $overdueCount > 0    ? '#b91c1c' : '#8a8880', route('invoices.index'),    $overdueCount > 0    ? 'badge-red'   : null],
            ] as [$label, $value, $bg, $color, $href, $badgeClass])
                <a href="{{ $href }}" style="background:#fff;border-radius:10px;padding:14px 16px;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,0.06);display:flex;flex-direction:column;gap:6px;transition:transform .12s"
                   onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div style="font-size:10px;color:#8a8880;font-weight:500;text-transform:uppercase;letter-spacing:.04em">{{ $label }}</div>
                        @if($badgeClass && $value > 0)
                            <span class="badge {{ $badgeClass }}">!</span>
                        @endif
                    </div>
                    <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,2.5vw,22px);color:{{ $color }}">{{ $value }}</div>
                </a>
            @endforeach
        </div>

        {{-- Collection donut ── --}}
        <div class="card kpi-collection" style="padding:20px 22px">
            <div style="font-size:13px;font-weight:600;color:#111110;margin-bottom:4px">Collection Status</div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:14px">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</div>
            <div class="collection-inner" style="display:flex;align-items:center;gap:20px">
                {{-- Donut --}}
                <div style="flex-shrink:0">
                    <svg width="144" height="144" viewBox="0 0 144 144">
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="#f0f2f0" stroke-width="20"/>
                        @if($collectedPct > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="#1a6b52" stroke-width="20"
                                    stroke-dasharray="{{ $collectedDash }} {{ $circumference - $collectedDash }}"
                                    stroke-linecap="butt"
                                    transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                        @endif
                        <text x="{{ $cx }}" y="{{ $cy - 7 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="20" font-weight="700" fill="#111110">{{ $collectionRate }}%</text>
                        <text x="{{ $cx }}" y="{{ $cy + 11 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="9" fill="#8a8880">collected</text>
                    </svg>
                </div>
                {{-- Legend --}}
                <div style="flex:1;min-width:0;display:grid;gap:8px">
                    <div style="background:#f0f9f5;border-radius:8px;padding:10px 12px">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                            <div style="width:8px;height:8px;border-radius:50%;background:#1a6b52;flex-shrink:0"></div>
                            <span style="font-size:11px;color:#166534;font-weight:500">Collected</span>
                            <span style="font-size:10px;color:#166534;margin-left:auto">{{ number_format($collectedPct,1) }}%</span>
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:16px;color:#166534">{{ currency($collectedThisMonth) }}</div>
                    </div>
                    <div style="background:#fef2f2;border-radius:8px;padding:10px 12px">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                            <div style="width:8px;height:8px;border-radius:50%;background:#b91c1c;flex-shrink:0"></div>
                            <span style="font-size:11px;color:#991b1b;font-weight:500">Outstanding</span>
                            <span style="font-size:10px;color:#991b1b;margin-left:auto">{{ number_format($outstandingPct,1) }}%</span>
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:16px;color:#991b1b">{{ currency($outstandingThisMonth) }}</div>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:0 2px">
                        <span style="color:#8a8880">Expected</span>
                        <span style="font-weight:600;color:#111110">{{ currency($expectedThisMonth) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 2: Chart + Recent payments ── --}}
    <div class="dash-row2">

        {{-- Income vs Expenses chart ── --}}
        <div class="card-dark" style="padding:22px 24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#fff">Income vs Expenses</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:2px">Last 6 months</div>
                </div>
                <div style="display:flex;align-items:center;gap:14px;font-size:11px">
                    <div style="display:flex;align-items:center;gap:5px">
                        <div style="width:9px;height:9px;border-radius:2px;background:#2ecc71"></div>
                        <span style="color:rgba(255,255,255,0.4)">Income</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:5px">
                        <div style="width:9px;height:9px;border-radius:2px;background:#f87171"></div>
                        <span style="color:rgba(255,255,255,0.4)">Expenses</span>
                    </div>
                </div>
            </div>

            @php
                $maxValue   = collect($chartData)->max(fn($d) => max($d['income'], $d['expenses']));
                $maxValue   = $maxValue > 0 ? $maxValue * 1.15 : 1;
                $currSymbol = currency_symbol();
            @endphp

            <div style="overflow-x:auto">
                <div style="min-width:280px">
                    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:6px;height:170px;padding-bottom:28px;position:relative">
                        @foreach([0,25,50,75,100] as $pct)
                            <div style="position:absolute;left:0;right:0;bottom:{{ $pct*1.42+28 }}px;border-top:1px dashed rgba(255,255,255,0.05);z-index:0">
                                <span style="font-size:9px;color:rgba(255,255,255,0.18);position:absolute;left:0;top:-7px">
                                    {{ $pct > 0 ? $currSymbol.' '.number_format($maxValue*$pct/100/1000,0).'k' : '0' }}
                                </span>
                            </div>
                        @endforeach
                        @foreach($chartData as $data)
                            @php
                                $incomeH   = $maxValue > 0 ? ($data['income']   / $maxValue) * 142 : 0;
                                $expensesH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 142 : 0;
                            @endphp
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1">
                                <div style="display:flex;gap:3px;align-items:flex-end;width:100%;justify-content:center;margin-bottom:6px">
                                    <div style="flex:1;max-width:18px">
                                        <div style="width:100%;background:#2ecc71;border-radius:3px 3px 0 0;height:{{ $incomeH }}px;min-height:{{ $data['income']>0?2:0 }}px"
                                             title="Income: {{ currency($data['income']) }}"></div>
                                    </div>
                                    <div style="flex:1;max-width:18px">
                                        <div style="width:100%;background:#f87171;border-radius:3px 3px 0 0;height:{{ $expensesH }}px;min-height:{{ $data['expenses']>0?2:0 }}px"
                                             title="Expenses: {{ currency($data['expenses']) }}"></div>
                                    </div>
                                </div>
                                <div style="font-size:9px;color:rgba(255,255,255,0.3);white-space:nowrap;text-align:center">{{ $data['label'] }}</div>
                                <div style="font-size:9px;font-weight:700;margin-top:1px;color:{{ $data['profit']>=0?'#2ecc71':'#f87171' }}">
                                    {{ $data['profit']>=0?'+':'' }}{{ number_format($data['profit']/1000,0) }}k
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @php
                $totalIncome   = collect($chartData)->sum('income');
                $totalExpenses = collect($chartData)->sum('expenses');
                $totalProfit   = collect($chartData)->sum('profit');
            @endphp
            <div class="dash-summary">
                @foreach([
                    ['6 mo income',   $totalIncome,   '#2ecc71'],
                    ['6 mo expenses', $totalExpenses, '#f87171'],
                    ['6 mo profit',   $totalProfit,   $totalProfit >= 0 ? '#2ecc71' : '#f87171'],
                ] as [$label, $value, $color])
                    <div>
                        <div style="font-size:10px;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ $label }}</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:clamp(14px,2vw,18px);color:{{ $color }}">
                            {{ $label === '6 mo profit' && $totalProfit < 0 ? '-' : '' }}{{ currency(abs($value)) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent payments --}}
        <div class="card" style="overflow:hidden;display:flex;flex-direction:column">
            <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:13px;font-weight:600;color:#111110">Recent payments</div>
                <a href="{{ route('payments.index') }}" style="font-size:12px;color:#1a6b52;text-decoration:none;font-weight:500">View all →</a>
            </div>
            @if($recentPayments->isEmpty())
                <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px;flex:1;display:flex;align-items:center;justify-content:center">
                    No payments recorded yet
                </div>
            @else
                <div style="flex:1;overflow-y:auto">
                    @foreach($recentPayments as $payment)
                        <div style="padding:11px 18px;border-bottom:1px solid rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:space-between;gap:8px">
                            <div style="display:flex;align-items:center;gap:10px;min-width:0">
                                <div class="avatar" style="background:#e6f2ed;color:#1a6b52">
                                    {{ $payment->tenant ? strtoupper(substr($payment->tenant->first_name,0,1).substr($payment->tenant->last_name,0,1)) : '?' }}
                                </div>
                                <div style="min-width:0">
                                    <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#111110">{{ $payment->tenant?->full_name ?? 'Unknown' }}</div>
                                    <div style="font-size:11px;color:#8a8880;margin-top:1px;display:flex;align-items:center;gap:5px">
                                        {{ $payment->payment_date->format('d M') }}
                                        <span style="background:#f0f2f0;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:600;color:#8a8880">{{ strtoupper($payment->method) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:#15803d;flex-shrink:0">+{{ currency($payment->amount) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ── Row 3: Highest balances + Property overview ── --}}
    <div class="dash-row3">

        {{-- Highest balances --}}
        <div class="card" style="overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#111110">Highest balances</div>
                    <div style="font-size:11px;color:#8a8880;margin-top:1px">Tenants with unpaid rent</div>
                </div>
                <a href="{{ route('reports.outstanding') }}" style="font-size:12px;color:#1a6b52;text-decoration:none;font-weight:500">Report →</a>
            </div>
            @if($tenantsWithBalance->isEmpty())
                <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px">
                    <div style="font-size:28px;margin-bottom:8px">✓</div>
                    All tenants are up to date
                </div>
            @else
                @foreach($tenantsWithBalance as $item)
                    <div style="padding:11px 18px;border-bottom:1px solid rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:space-between;gap:8px">
                        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                            <div class="avatar" style="background:#fee2e2;color:#b91c1c">
                                {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                            </div>
                            <div style="min-width:0">
                                <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#111110">{{ $item['tenant']->full_name }}</div>
                                <div style="font-size:11px;color:#8a8880;margin-top:1px">Unit {{ $item['unit']->name }} · {{ $item['property']->name }}</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                            <div style="font-size:13px;font-weight:700;color:#b91c1c">{{ currency($item['balance']) }}</div>
                            <form method="POST" action="{{ route('communications.send') }}">
                                @csrf
                                <input type="hidden" name="recipient_type" value="individual">
                                <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                                <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                                <button type="submit" style="font-size:11px;padding:4px 10px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600">
                                    Remind
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Property overview --}}
        <div class="card" style="overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#111110">Property overview</div>
                    <div style="font-size:11px;color:#8a8880;margin-top:1px">Occupancy by property</div>
                </div>
                <a href="{{ route('properties.index') }}" style="font-size:12px;color:#1a6b52;text-decoration:none;font-weight:500">Manage →</a>
            </div>
            @if($propertiesOverview->isEmpty())
                <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px">No properties added yet</div>
            @else
                @foreach($propertiesOverview as $prop)
                    @php
                        $rate   = $prop->units_count > 0
                            ? round(($prop->occupied_count / $prop->units_count) * 100) : 0;
                        $isGood = $rate >= 80;
                    @endphp
                    <a href="{{ route('properties.show', $prop) }}"
                       style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid rgba(0,0,0,0.04);text-decoration:none;transition:background .12s"
                       onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                        <div style="width:38px;height:38px;border-radius:8px;background:{{ $isGood ? '#e6f2ed' : '#fee2e2' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="16" height="16" viewBox="0 0 14 14" fill="none"><path d="M7 1L1 6v7h3.5V9.5h5V13H13V6L7 1z" stroke="{{ $isGood ? '#1a6b52' : '#b91c1c' }}" stroke-width="1.3" stroke-linejoin="round"/></svg>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:600;color:#111110;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $prop->name }}</div>
                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                <div style="flex:1;height:4px;background:#f0f2f0;border-radius:2px;overflow:hidden">
                                    <div style="height:100%;background:{{ $isGood ? '#1a6b52' : '#b91c1c' }};width:{{ $rate }}%"></div>
                                </div>
                                <span style="font-size:11px;color:#8a8880;white-space:nowrap">{{ $prop->occupied_count }}/{{ $prop->units_count }}</span>
                            </div>
                        </div>
                        <div style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;
                            background:{{ $isGood ? '#dcfce7' : '#fee2e2' }};
                            color:{{ $isGood ? '#15803d' : '#b91c1c' }};
                            flex-shrink:0">
                            {{ $rate }}%
                        </div>
                    </a>
                @endforeach
            @endif
        </div>

    </div>

</div>
</x-layouts.app>