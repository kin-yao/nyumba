<x-layouts.app>
<style>
.report-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.util-type-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.tbl-scroll {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.tbl-scroll table {
    width: 100%;
    border-collapse: collapse;
    min-width: 560px;
}

.util-cards { display: none; }
.util-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 12px 14px;
    margin-bottom: 6px;
}

@media (max-width: 700px) {
    .util-type-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .util-cards  { display: block; }
}
</style>

<div class="report-wrap">

    <div class="report-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
                &rsaquo; Utilities
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Utilities</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </div>
        </div>
        <form method="GET" action="{{ route('reports.utilities') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex-shrink:0">
            <select name="month" onchange="this.form.submit()"
                    style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                        {{ \Carbon\Carbon::createFromDate($year,$m,1)->format('F') }}
                    </option>
                @endforeach
            </select>
            <input name="year" type="number" value="{{ $year }}" onchange="this.form.submit()"
                   style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:80px">
        </form>
    </div>

    @if($missingCount > 0)
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#92400e">
            {{ $missingCount }} {{ Str::plural('reading', $missingCount) }} not entered for this period —
            <a href="{{ route('utilities.index', ['month' => $month, 'year' => $year]) }}" style="color:#92400e;font-weight:500">enter readings</a>.
        </div>
    @endif

    {{-- Totals by utility type --}}
    @if(count($totalsByType))
        <div class="util-type-grid">
            @foreach($totalsByType as $typeName => $amount)
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 18px">
                    <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">{{ $typeName }}</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:clamp(16px,2.5vw,22px);color:#0369a1">{{ currency($amount) }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 18px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
        <span style="font-size:13px;color:#8a8880">Total billed (all utilities)</span>
        <span style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,24px);color:#0369a1">{{ currency($grandTotal) }}</span>
    </div>

    @if(empty($rows))
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:40px;text-align:center;color:#8a8880;font-size:13px">
            No utility rates configured, or no occupied units, for this period.
        </div>
    @else
        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Utility</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Consumed</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Charge</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $row['property'] }}</td>
                            <td style="padding:11px 14px;font-size:13px">{{ $row['unit'] }}</td>
                            <td style="padding:11px 14px;font-size:13px">{{ $row['tenant'] }}</td>
                            <td style="padding:11px 14px;font-size:13px">{{ $row['utility_name'] }}</td>
                            <td style="padding:11px 14px;font-size:13px;text-align:right">
                                @if($row['has_reading'])
                                    {{ number_format($row['consumed'], 1) }}
                                @else
                                    <span style="color:#d97706">No reading</span>
                                @endif
                            </td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right;color:{{ $row['has_reading'] ? '#0369a1' : '#8a8880' }}">
                                {{ $row['has_reading'] ? currency($row['charge']) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="util-cards">
            @foreach($rows as $row)
                <div class="util-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px">
                        <div style="min-width:0">
                            <div style="font-weight:500;font-size:13px">{{ $row['tenant'] }}</div>
                            <div style="font-size:11px;color:#8a8880">{{ $row['property'] }} &middot; Unit {{ $row['unit'] }}</div>
                        </div>
                        <div style="font-size:14px;font-weight:600;color:{{ $row['has_reading'] ? '#0369a1' : '#8a8880' }};flex-shrink:0">
                            {{ $row['has_reading'] ? currency($row['charge']) : '—' }}
                        </div>
                    </div>
                    <div style="font-size:11px;color:#8a8880">
                        {{ $row['utility_name'] }}
                        @if($row['has_reading'])
                            &middot; {{ number_format($row['consumed'], 1) }} units
                        @else
                            &middot; <span style="color:#d97706">No reading</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>