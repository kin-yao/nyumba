<x-layouts.app>
<style>
.rep-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.rep-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

@media (max-width: 700px) {
    .rep-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 420px) {
    .rep-grid { grid-template-columns: 1fr; }
}
</style>

<div class="rep-wrap">

    <div style="margin-bottom:24px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Reports</div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">Generate and export data</div>
    </div>

    {{-- ── Combined PDF download ── --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:24px">
        <div style="font-size:14px;font-weight:500;margin-bottom:3px">Download a report</div>
        <div style="font-size:12px;color:#8a8880;margin-bottom:16px;line-height:1.5">
            Pick a property and a period.
        </div>

        @if($properties->isEmpty())
            <div style="font-size:13px;color:#8a8880">Add a property first to generate its report.</div>
        @else
            <form method="GET" action="{{ route('reports.download') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <select name="property_id" required
                        style="height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;min-width:180px">
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </select>

                <select name="month"
                        style="height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <option value="">Full year summary</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year" required
                        style="height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @foreach(range(now()->year, now()->year - 5) as $y)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        style="height:36px;padding:0 18px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer">
                    Download PDF
                </button>
            </form>
        @endif
    </div>

    <div style="font-size:12px;color:#8a8880;margin-bottom:10px">Or browse a report in your browser:</div>

    <div class="rep-grid">
        @foreach([
            [route('reports.rent-roll'),        'Rent roll',            'Units, tenants, expected vs collected per month',         '#e6f2ed', '#1a6b52', '<rect x="2" y="2" width="12" height="12" rx="2" stroke="#1a6b52" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="#1a6b52" stroke-width="1.2" stroke-linecap="round"/>'],
            [route('reports.outstanding'),      'Outstanding balances',  'Tenants with unpaid balances sorted by amount',           '#fee2e2', '#b91c1c', '<circle cx="8" cy="8" r="6" stroke="#b91c1c" stroke-width="1.3"/><path d="M8 5v3.5M8 11v.3" stroke="#b91c1c" stroke-width="1.3" stroke-linecap="round"/>'],
            [route('reports.collections'),      'Collection report',    'Rent payments received broken down by method',             '#e6f2ed', '#1a6b52', '<path d="M2 13V8l3-3 3 2 3-4 3 2" stroke="#1a6b52" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>'],
            [route('reports.income-expenses'),  'Income vs Expenses',   'Revenue, costs and net profit for a period',               '#e6f2ed', '#1a6b52', '<path d="M2 13V5l5 4 4-6 3 3" stroke="#1a6b52" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>'],
            [route('reports.deposits'),         'Deposits held',        'Security deposits collected, outstanding and held',        '#fef3c7', '#92400e', '<rect x="3" y="7" width="10" height="7" rx="1.5" stroke="#92400e" stroke-width="1.3"/><path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="#92400e" stroke-width="1.3" stroke-linecap="round"/>'],
            [route('reports.tenant-statement'), 'Tenant statement',     'Full ledger for an individual tenant',                     '#e6f2ed', '#1a6b52', '<path d="M4 2h8v12l-2-1.5L8 14l-2-1.5L4 14V2z" stroke="#1a6b52" stroke-width="1.3" stroke-linejoin="round"/><path d="M6 6h4M6 9h3" stroke="#1a6b52" stroke-width="1.2" stroke-linecap="round"/>'],
            [route('reports.occupancy'),        'Occupancy report',     'Vacant vs occupied across all properties',                 '#e6f2ed', '#1a6b52', '<rect x="1" y="1" width="6" height="6" rx="1.5" fill="#e6f2ed" stroke="#1a6b52" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="#e6f2ed" stroke="#1a6b52" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="#1a6b52" stroke-width="1.2" stroke-dasharray="2 1.5"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="#e6f2ed" stroke="#1a6b52" stroke-width="1.2"/>'],
        ] as [$href, $title, $desc, $iconBg, $iconColor, $iconPath])
            <a href="{{ $href }}"
               style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;text-decoration:none;color:inherit;display:block">
                <div style="width:36px;height:36px;border-radius:8px;background:{{ $iconBg }};display:flex;align-items:center;justify-content:center;margin-bottom:12px;flex-shrink:0">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        {!! $iconPath !!}
                    </svg>
                </div>
                <div style="font-size:14px;font-weight:500;margin-bottom:4px">{{ $title }}</div>
                <div style="font-size:12px;color:#8a8880;line-height:1.5">{{ $desc }}</div>
            </a>
        @endforeach
    </div>
</div>
</x-layouts.app>