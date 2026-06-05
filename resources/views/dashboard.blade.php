<x-layouts.app>
<style>
.dash-wrap { padding: clamp(16px, 4vw, 34px); padding-bottom: 48px; }

/* Row 1: collection chart + 2 stat cards */
.dash-row1 {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 14px;
}

/* Row 2: 4 KPI cards */
.dash-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}

/* Row 3: recent payments + balances */
.dash-row3 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

/* Row 4: property cards */
.dash-props {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
}

/* Row 5: chart summary */
.dash-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid rgba(0,0,0,0.07);
}

/* Collection card inner layout */
.collection-inner {
    display: flex;
    align-items: center;
    gap: 24px;
}

@media (max-width: 900px) {
    .dash-row1 { grid-template-columns: 1fr 1fr; }
    .dash-row1 .collection-card { grid-column: 1 / -1; }
    .dash-kpi { grid-template-columns: repeat(2, 1fr); }
    .dash-props { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .dash-row1 { grid-template-columns: 1fr; }
    .dash-row1 .collection-card { grid-column: auto; }
    .collection-inner { flex-direction: column; align-items: flex-start; gap: 16px; }
    .collection-inner svg { align-self: center; }
    .dash-kpi { grid-template-columns: repeat(2, 1fr); }
    .dash-row3 { grid-template-columns: 1fr; }
    .dash-props { grid-template-columns: 1fr; }
    .dash-summary { grid-template-columns: 1fr; gap: 8px; }
}
</style>

<div class="dash-wrap">

    {{-- ── Expired / Trial-ended banner ────────────────────────────────── --}}
    @php $account = auth()->user()->account; @endphp
    @if($account && $account->isExpired())
        <div style="background:#fff;border:2px solid #b91c1c;border-radius:12px;padding:28px 32px;margin-bottom:28px;text-align:center">

            <div style="font-size:36px;margin-bottom:12px">🔒</div>

            <div style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:8px">
                @if($account->plan === 'explore')
                    Your free trial has ended
                @else
                    Your {{ ucfirst($account->plan) }} subscription has expired
                @endif
            </div>

            <div style="font-size:13px;color:#8a8880;margin-bottom:24px;max-width:500px;margin-left:auto;margin-right:auto;line-height:1.7">
                @if($account->plan === 'explore')
                    Your 30-day free trial ended on {{ $account->trial_ends_at?->format('d M Y') }}.
                    Upgrade to a paid plan to keep managing your properties, tenants and invoices.
                @else
                    Your subscription expired on {{ $account->plan_expires_at?->format('d M Y') }}.
                    Renew to restore automated invoicing, payment tracking and SMS alerts.
                @endif
                <br><strong style="color:#111110">Your data is safe and fully retained.</strong>
            </div>

            {{-- Plans --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px;text-align:left;max-width:560px;margin-left:auto;margin-right:auto">
                @foreach([
                    ['name'=>'Starter', 'price'=>'2,000', 'units'=>20,  'sms'=>50,  'popular'=>false],
                    ['name'=>'Growth',  'price'=>'4,500', 'units'=>50,  'sms'=>100, 'popular'=>true],
                    ['name'=>'Pro',     'price'=>'7,000', 'units'=>100, 'sms'=>200, 'popular'=>false],
                ] as $plan)
                    <div style="background:#f9faf9;border-radius:10px;border:{{ $plan['popular'] ? '2px solid #1a6b52' : '1px solid rgba(0,0,0,0.07)' }};padding:16px;position:relative">
                        @if($plan['popular'])
                            <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#1a6b52;color:#fff;font-size:9px;font-weight:600;padding:2px 10px;border-radius:10px;white-space:nowrap">
                                POPULAR
                            </div>
                        @endif
                        <div style="font-size:11px;font-weight:500;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">
                            {{ $plan['name'] }}
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:2px">
                            KES {{ $plan['price'] }}<span style="font-size:12px;font-family:'DM Sans',sans-serif;color:#8a8880">/mo</span>
                        </div>
                        <div style="font-size:11px;color:#8a8880;margin-top:6px">{{ $plan['units'] }} units</div>
                        <div style="font-size:11px;color:#8a8880">{{ $plan['sms'] }} SMS/month</div>
                    </div>
                @endforeach
            </div>

            {{-- WhatsApp CTA --}}
            <a href="https://wa.me/254705056343?text=Hi%2C%20I%20would%20like%20to%20upgrade%20my%20Nyumba%20subscription%20for%20account%3A%20{{ urlencode($account->name) }}"
               target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:11px 28px;background:#25D366;color:#fff;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;margin-bottom:12px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Contact us on WhatsApp to upgrade
            </a>

            <div style="font-size:11px;color:#8a8880;margin-top:4px">
                Pay 6 months, get 1 month free &middot; Pay 12 months, get 2 months free
            </div>
        </div>
    @endif
    {{-- ── End expired banner ───────────────────────────────────────────── --}}

    {{-- Header --}}
    <div style="margin-bottom:20px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.2">
            Hello {{ explode(' ', auth()->user()->name)[0] }}, Karibu nyumbani!
        </div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">
            {{ now()->format('l, d F Y') }}
            &middot; {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }} overview
        </div>
    </div>

    {{-- Alerts --}}
    @if($urgentMaintenance > 0)
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:12px;font-size:13px;color:#991b1b;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
            <span>⚠ {{ $urgentMaintenance }} urgent maintenance {{ Str::plural('request', $urgentMaintenance) }} need attention</span>
            <a href="{{ route('maintenance.index') }}" style="color:#991b1b;font-weight:500;text-decoration:none;white-space:nowrap">View →</a>
        </div>
    @endif

    @if($overdueCount > 0)
        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:11px 15px;margin-bottom:12px;font-size:13px;color:#92400e;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
            <span>{{ $overdueCount }} overdue {{ Str::plural('invoice', $overdueCount) }} require follow up</span>
            <a href="{{ route('invoices.index') }}" style="color:#92400e;font-weight:500;text-decoration:none;white-space:nowrap">View →</a>
        </div>
    @endif

    {{-- Row 1: Collection + Net Profit + Occupancy --}}
    @php
        $totalExpected  = $expectedThisMonth > 0 ? $expectedThisMonth : 1;
        $collectedPct   = min(100, ($collectedThisMonth / $totalExpected) * 100);
        $outstandingPct = 100 - $collectedPct;
        $radius         = 60;
        $cx = $cy       = 80;
        $circumference  = 2 * M_PI * $radius;
        $collectedDash  = ($collectedPct / 100) * $circumference;
    @endphp

    <div class="dash-row1">

        {{-- Collection card --}}
        <div class="collection-card" style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
            <div class="collection-inner">
                <div style="flex-shrink:0">
                    <svg width="140" height="140" viewBox="0 0 160 160">
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="#fee2e2" stroke-width="22"/>
                        @if($collectedPct > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="#1a6b52" stroke-width="22"
                                    stroke-dasharray="{{ $collectedDash }} {{ $circumference - $collectedDash }}"
                                    transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                        @endif
                        <text x="{{ $cx }}" y="{{ $cy - 8 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="20" font-weight="bold" fill="#111110">{{ $collectionRate }}%</text>
                        <text x="{{ $cx }}" y="{{ $cy + 10 }}" text-anchor="middle" font-family="DM Sans,sans-serif" font-size="10" fill="#8a8880">collected</text>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:500;margin-bottom:12px">
                        Collection status
                        <span style="font-size:11px;color:#8a8880;font-weight:400;margin-left:4px">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:#e6f2ed;border-radius:8px;margin-bottom:6px">
                        <div style="display:flex;align-items:center;gap:7px">
                            <div style="width:8px;height:8px;border-radius:50%;background:#1a6b52;flex-shrink:0"></div>
                            <div>
                                <div style="font-size:12px;font-weight:500;color:#166534">Collected</div>
                                <div style="font-size:10px;color:#166534">{{ number_format($collectedPct,1) }}% of expected</div>
                            </div>
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:18px;color:#166534">{{ currency($collectedThisMonth) }}</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:#fee2e2;border-radius:8px;margin-bottom:6px">
                        <div style="display:flex;align-items:center;gap:7px">
                            <div style="width:8px;height:8px;border-radius:50%;background:#b91c1c;flex-shrink:0"></div>
                            <div>
                                <div style="font-size:12px;font-weight:500;color:#991b1b">Outstanding</div>
                                <div style="font-size:10px;color:#991b1b">{{ number_format($outstandingPct,1) }}% of expected</div>
                            </div>
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:18px;color:#991b1b">{{ currency($outstandingThisMonth) }}</div>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:6px 4px;font-size:12px">
                        <span style="color:#8a8880">Total expected</span>
                        <span style="font-weight:500">{{ currency($expectedThisMonth) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net profit --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
            <div style="font-size:10px;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:7px">Net profit this month</div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,27px);color:{{ $netProfitThisMonth >= 0 ? '#15803d' : '#b91c1c' }}">
                {{ $netProfitThisMonth < 0 ? '-' : '' }}{{ currency(abs($netProfitThisMonth)) }}
            </div>
            <div style="font-size:12px;color:#8a8880;margin-top:6px;line-height:1.6">
                <div>Income: {{ currency($paymentsThisMonth) }}</div>
                <div>Expenses: {{ currency($expensesThisMonth) }}</div>
            </div>
            <div style="margin-top:8px;font-size:11px;color:{{ $netProfitThisMonth >= 0 ? '#15803d' : '#b91c1c' }}">
                {{ $netProfitThisMonth >= 0 ? 'Profitable month ↑' : 'Net loss this month ↓' }}
            </div>
        </div>

        {{-- Occupancy --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
            <div style="font-size:10px;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:7px">Occupancy</div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,27px);color:{{ $occupancyRate >= 80 ? '#15803d' : '#b91c1c' }}">
                {{ $occupancyRate }}%
            </div>
            <div style="font-size:12px;color:#8a8880;margin-top:6px;line-height:1.6">
                <div>{{ $occupiedUnits }} occupied</div>
                <div>{{ $vacantUnits }} vacant</div>
            </div>
            <div style="margin-top:10px;height:4px;background:#ece9e2;border-radius:2px;overflow:hidden">
                <div style="height:100%;background:{{ $occupancyRate >= 80 ? '#1a6b52' : '#b91c1c' }};border-radius:2px;width:{{ $occupancyRate }}%"></div>
            </div>
        </div>
    </div>

    {{-- Row 2: KPIs --}}
    <div class="dash-kpi">
        @foreach([
            ['Properties',       $totalProperties, null,                                   route('properties.index')],
            ['Active tenants',   $totalTenants,    null,                                   route('tenants.index')],
            ['Open maintenance', $openMaintenance, $openMaintenance > 0 ? '#d97706':null,  route('maintenance.index')],
            ['Overdue invoices', $overdueCount,    $overdueCount > 0    ? '#b91c1c':null,  route('invoices.index')],
        ] as [$label, $value, $color, $href])
            <a href="{{ $href }}" style="text-decoration:none;background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 20px;display:block">
                <div style="font-size:10px;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px">{{ $label }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,24px);color:{{ $color ?? '#111110' }}">{{ $value }}</div>
            </a>
        @endforeach
    </div>

    {{-- Row 3: Recent payments + Highest balances --}}
    <div class="dash-row3">

        {{-- Recent payments --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:13px;font-weight:500">Recent payments</div>
                <a href="{{ route('payments.index') }}" style="font-size:12px;color:#1a6b52;text-decoration:none">View all</a>
            </div>
            @if($recentPayments->isEmpty())
                <div style="padding:32px;text-align:center;color:#8a8880;font-size:13px">No payments recorded yet</div>
            @else
                @foreach($recentPayments as $payment)
                    <div style="padding:11px 18px;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between;gap:8px">
                        <div style="display:flex;align-items:center;gap:9px;min-width:0">
                            <div style="width:28px;height:28px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                {{ $payment->tenant ? strtoupper(substr($payment->tenant->first_name,0,1).substr($payment->tenant->last_name,0,1)) : '?' }}
                            </div>
                            <div style="min-width:0">
                                <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $payment->tenant?->full_name ?? 'Unknown' }}</div>
                                <div style="font-size:11px;color:#8a8880">{{ $payment->payment_date->format('d M Y') }} &middot; {{ strtoupper($payment->method) }}</div>
                            </div>
                        </div>
                        <div style="font-size:13px;font-weight:500;color:#15803d;flex-shrink:0">{{ currency($payment->amount) }}</div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Highest balances --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:13px;font-weight:500">Highest balances</div>
                <a href="{{ route('reports.outstanding') }}" style="font-size:12px;color:#1a6b52;text-decoration:none">Full report</a>
            </div>
            @if($tenantsWithBalance->isEmpty())
                <div style="padding:32px;text-align:center;color:#8a8880;font-size:13px">All tenants are up to date ✓</div>
            @else
                @foreach($tenantsWithBalance as $item)
                    <div style="padding:11px 18px;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between;gap:8px">
                        <div style="display:flex;align-items:center;gap:9px;flex:1;min-width:0">
                            <div style="width:28px;height:28px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#b91c1c;flex-shrink:0">
                                {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                            </div>
                            <div style="min-width:0">
                                <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item['tenant']->full_name }}</div>
                                <div style="font-size:11px;color:#8a8880">Unit {{ $item['unit']->name }} &middot; {{ $item['property']->name }}</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                            <div style="font-size:13px;font-weight:600;color:#b91c1c">{{ currency($item['balance']) }}</div>
                            <form method="POST" action="{{ route('communications.send') }}">
                                @csrf
                                <input type="hidden" name="recipient_type" value="individual">
                                <input type="hidden" name="tenant_id" value="{{ $item['tenant']->id }}">
                                <input type="hidden" name="message" value="Dear {{ $item['tenant']->first_name }}, your outstanding balance is {{ currency($item['balance']) }}. Please make payment at your earliest convenience. Thank you.">
                                <button type="submit" style="font-size:11px;padding:4px 9px;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                                    Remind
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Row 4: Property overview --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden;margin-bottom:14px">
        <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:13px;font-weight:500">Property overview</div>
            <a href="{{ route('properties.index') }}" style="font-size:12px;color:#1a6b52;text-decoration:none">Manage</a>
        </div>
        @if($propertiesOverview->isEmpty())
            <div style="padding:32px;text-align:center;color:#8a8880;font-size:13px">No properties added yet</div>
        @else
            <div class="dash-props">
                @foreach($propertiesOverview as $property)
                    @php
                        $rate = $property->units_count > 0
                            ? round(($property->occupied_count / $property->units_count) * 100)
                            : 0;
                    @endphp
                    <a href="{{ route('properties.show', $property) }}"
                       style="padding:16px 18px;border-right:1px solid rgba(0,0,0,0.06);border-bottom:1px solid rgba(0,0,0,0.06);text-decoration:none;color:inherit;display:block">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                            <div>
                                <div style="font-size:13px;font-weight:500">{{ $property->name }}</div>
                                <div style="font-size:11px;color:#8a8880;margin-top:1px">
                                    {{ $property->area ?? $property->county ?? '' }} &middot; {{ ucfirst($property->type) }}
                                </div>
                            </div>
                            <span style="font-size:12px;font-weight:600;color:{{ $rate >= 80 ? '#15803d' : '#b91c1c' }}">{{ $rate }}%</span>
                        </div>
                        <div style="display:flex;gap:12px;font-size:12px;color:#8a8880;margin-bottom:8px;flex-wrap:wrap">
                            <span>{{ $property->units_count }} units</span>
                            <span style="color:#15803d">{{ $property->occupied_count }} occupied</span>
                            <span style="color:{{ ($property->units_count-$property->occupied_count)>0?'#b91c1c':'#8a8880' }}">{{ $property->units_count - $property->occupied_count }} vacant</span>
                        </div>
                        <div style="height:4px;background:#ece9e2;border-radius:2px;overflow:hidden">
                            <div style="height:100%;background:{{ $rate >= 80 ? '#1a6b52' : '#b91c1c' }};border-radius:2px;width:{{ $rate }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Row 5: Income vs Expenses chart --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
            <div>
                <div style="font-size:13px;font-weight:500">Income vs Expenses</div>
                <div style="font-size:12px;color:#8a8880;margin-top:2px">Last 6 months</div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;font-size:12px">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:10px;height:10px;border-radius:2px;background:#1a6b52"></div>
                    <span style="color:#8a8880">Income</span>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:10px;height:10px;border-radius:2px;background:#fee2e2;border:1px solid #fca5a5"></div>
                    <span style="color:#8a8880">Expenses</span>
                </div>
            </div>
        </div>

        @php
            $maxValue   = collect($chartData)->max(fn($d) => max($d['income'], $d['expenses']));
            $maxValue   = $maxValue > 0 ? $maxValue * 1.15 : 1;
            $currSymbol = currency_symbol();
        @endphp

        <div style="overflow-x:auto">
            <div style="min-width:300px">
                <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px;height:180px;padding-bottom:28px;position:relative">
                    @foreach([0,25,50,75,100] as $pct)
                        <div style="position:absolute;left:0;right:0;bottom:{{ $pct*1.52+28 }}px;border-top:1px dashed rgba(0,0,0,0.06);z-index:0">
                            <span style="font-size:10px;color:#c4c2be;position:absolute;left:0;top:-8px">
                                {{ $pct > 0 ? $currSymbol.' '.number_format($maxValue*$pct/100/1000,0).'k' : '0' }}
                            </span>
                        </div>
                    @endforeach
                    @foreach($chartData as $data)
                        @php
                            $incomeH   = $maxValue > 0 ? ($data['income']   / $maxValue) * 152 : 0;
                            $expensesH = $maxValue > 0 ? ($data['expenses'] / $maxValue) * 152 : 0;
                        @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1">
                            <div style="display:flex;gap:3px;align-items:flex-end;width:100%;justify-content:center;margin-bottom:6px">
                                <div style="flex:1;max-width:22px">
                                    <div style="width:100%;background:#1a6b52;border-radius:3px 3px 0 0;height:{{ $incomeH }}px;min-height:{{ $data['income']>0?2:0 }}px" title="Income: {{ currency($data['income']) }}"></div>
                                </div>
                                <div style="flex:1;max-width:22px">
                                    <div style="width:100%;background:#fca5a5;border-radius:3px 3px 0 0;height:{{ $expensesH }}px;min-height:{{ $data['expenses']>0?2:0 }}px" title="Expenses: {{ currency($data['expenses']) }}"></div>
                                </div>
                            </div>
                            <div style="font-size:10px;color:#8a8880;white-space:nowrap;text-align:center">{{ $data['label'] }}</div>
                            <div style="font-size:10px;font-weight:500;margin-top:2px;color:{{ $data['profit']>=0?'#15803d':'#b91c1c' }}">
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
            <div>
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">6 month income</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(16px,2.5vw,20px);color:#15803d">{{ currency($totalIncome) }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">6 month expenses</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(16px,2.5vw,20px);color:#b91c1c">{{ currency($totalExpenses) }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">6 month net profit</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(16px,2.5vw,20px);color:{{ $totalProfit>=0?'#15803d':'#b91c1c' }}">{{ currency(abs($totalProfit)) }}</div>
            </div>
        </div>
    </div>

</div>
</x-layouts.app>