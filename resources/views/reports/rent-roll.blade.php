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

.summary-bar {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 18px;
    margin-bottom: 16px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 13px;
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

.rr-cards { display: none; }
.rr-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.rr-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.rr-card-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    padding: 4px 0;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}

@media (max-width: 640px) {
    .tbl-scroll { display: none; }
    .rr-cards   { display: block; }
}
</style>

<div class="report-wrap">

    <div class="report-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
                &rsaquo; Rent roll
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Rent roll</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </div>
        </div>
        <form method="GET" action="{{ route('reports.rent-roll') }}"
              style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex-shrink:0">
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

    <div class="summary-bar">
        <div><span style="color:#8a8880">Expected:</span> <strong>{{ currency($totalExpected) }}</strong></div>
        <div><span style="color:#8a8880">Collected:</span> <strong style="color:#15803d">{{ currency($totalCollected) }}</strong></div>
        <div><span style="color:#8a8880">Outstanding:</span> <strong style="color:#b91c1c">{{ currency($totalOutstanding) }}</strong></div>
        <div><span style="color:#8a8880">Collection rate:</span> <strong>{{ $collectionRate }}%</strong></div>
    </div>

    @foreach($properties as $property)
        @php $occupiedUnits = $property->units->filter(fn($u) => $u->activeLease !== null); @endphp
        @if($occupiedUnits->isNotEmpty())
            <div style="margin-bottom:20px">
                <div style="font-size:13px;font-weight:500;margin-bottom:8px">{{ $property->name }}</div>

                {{-- Desktop table --}}
                <div class="tbl-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Phone</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Expected</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Collected</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Balance</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($occupiedUnits as $unit)
                                @php
                                    $lease    = $unit->activeLease;
                                    $tenant   = $lease->tenant;
                                    $invoice  = $lease->invoices->first();

                                    // Use invoice total as expected; fall back to monthly rent if no invoice
                                    $expected  = $invoice
                                        ? floatval($invoice->total_amount)
                                        : floatval($lease->monthly_rent);
                                    $collected = $invoice ? floatval($invoice->amount_paid) : 0;
                                    $balance   = $expected - $collected;

                                    if ($balance <= 0)       { $sb='#dcfce7';$st='#166634';$sl='Paid'; }
                                    elseif ($collected > 0)  { $sb='#fef3c7';$st='#92400e';$sl='Partial'; }
                                    else                     { $sb='#fee2e2';$st='#991b1b';$sl='Unpaid'; }
                                @endphp
                                <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                                    <td style="padding:11px 14px;font-size:13px;font-weight:500">{{ $unit->name }}</td>
                                    <td style="padding:11px 14px;font-size:13px">{{ $tenant->full_name }}</td>
                                    <td style="padding:11px 14px;font-size:12px;font-family:monospace;color:#8a8880">{{ $tenant->phone }}</td>
                                    <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right">
                                        {{ number_format($expected) }}
                                        @if(!$invoice)
                                            <div style="font-size:10px;color:#d97706;font-weight:400">No invoice</div>
                                        @endif
                                    </td>
                                    <td style="padding:11px 14px;font-size:13px;text-align:right;color:#15803d;font-weight:500">
                                        {{ number_format($collected) }}
                                    </td>
                                    <td style="padding:11px 14px;font-size:13px;text-align:right;color:{{ $balance>0?'#b91c1c':'#15803d' }};font-weight:500">
                                        {{ number_format(abs($balance)) }}
                                        @if($balance < 0)
                                            <div style="font-size:10px;color:#15803d;font-weight:400">Overpaid</div>
                                        @endif
                                    </td>
                                    <td style="padding:11px 14px">
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sb }};color:{{ $st }}">
                                            {{ $sl }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="rr-cards">
                    @foreach($occupiedUnits as $unit)
                        @php
                            $lease    = $unit->activeLease;
                            $tenant   = $lease->tenant;
                            $invoice  = $lease->invoices->first();
                            $expected  = $invoice
                                ? floatval($invoice->total_amount)
                                : floatval($lease->monthly_rent);
                            $collected = $invoice ? floatval($invoice->amount_paid) : 0;
                            $balance   = $expected - $collected;
                            if ($balance <= 0)       { $sb='#dcfce7';$st='#166634';$sl='Paid'; }
                            elseif ($collected > 0)  { $sb='#fef3c7';$st='#92400e';$sl='Partial'; }
                            else                     { $sb='#fee2e2';$st='#991b1b';$sl='Unpaid'; }
                        @endphp
                        <div class="rr-card">
                            <div class="rr-card-top">
                                <div>
                                    <div style="font-weight:500;font-size:13px">{{ $tenant->full_name }}</div>
                                    <div style="font-size:11px;color:#8a8880">
                                        Unit {{ $unit->name }} &middot; {{ $tenant->phone }}
                                    </div>
                                </div>
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sb }};color:{{ $st }};flex-shrink:0">
                                    {{ $sl }}
                                </span>
                            </div>
                            <div class="rr-card-row">
                                <span style="color:#8a8880">Expected</span>
                                <span style="font-weight:500">
                                    {{ currency($expected) }}
                                    @if(!$invoice) <span style="font-size:10px;color:#d97706">(no invoice)</span> @endif
                                </span>
                            </div>
                            <div class="rr-card-row">
                                <span style="color:#8a8880">Collected</span>
                                <span style="font-weight:500;color:#15803d">{{ currency($collected) }}</span>
                            </div>
                            <div class="rr-card-row" style="border:none">
                                <span style="color:#8a8880">Balance</span>
                                <span style="font-weight:600;color:{{ $balance>0?'#b91c1c':'#15803d' }}">
                                    {{ currency(abs($balance)) }}
                                    @if($balance < 0) <span style="font-size:10px">(overpaid)</span> @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
</x-layouts.app>