<x-layouts.app>
<style>
.dep-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }
.dep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dep-table th { font-size: 10px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase; color: #8a8880; padding: 8px 12px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.07); }
.dep-table td { padding: 11px 12px; border-bottom: 1px solid rgba(0,0,0,0.05); vertical-align: middle; }
.dep-table tr:last-child td { border-bottom: none; }
@media (max-width: 700px) {
    .dep-hide { display: none; }
}
</style>

<div class="dep-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
            &rsaquo; Deposits held
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Deposits held</div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">Security deposits collected across all active leases</div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('reports.deposits') }}"
          style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
        <select name="property_id" onchange="this.form.submit()"
                style="height:34px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
            <option value="">All properties</option>
            @foreach($properties as $p)
                <option value="{{ $p->id }}" {{ $propertyId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </form>

    {{-- Summary cards --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
            <div style="font-size:11px;color:#8a8880;margin-bottom:4px">Total required</div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px">{{ currency($totalRequired) }}</div>
        </div>
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
            <div style="font-size:11px;color:#8a8880;margin-bottom:4px">Total held</div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:#1a6b52">{{ currency($totalHeld) }}</div>
        </div>
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
            <div style="font-size:11px;color:#8a8880;margin-bottom:4px">Total outstanding</div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:{{ $totalOutstanding > 0 ? '#b91c1c' : '#1a6b52' }}">{{ currency($totalOutstanding) }}</div>
        </div>
    </div>

    {{-- Deposits table --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden;margin-bottom:28px">
        <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.07);font-size:13px;font-weight:500">
            Active leases — deposit status
        </div>
        @if($leases->isEmpty())
            <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px">No active leases found.</div>
        @else
            <div style="overflow-x:auto">
                <table class="dep-table">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Unit</th>
                            <th class="dep-hide">Property</th>
                            <th class="dep-hide">Move-in</th>
                            <th>Required</th>
                            <th>Held</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leases as $row)
                            @php
                                $badge = match($row['status']) {
                                    'paid'    => ['#dcfce7','#166534','Paid'],
                                    'partial' => ['#fef3c7','#92400e','Partial'],
                                    default   => ['#fee2e2','#991b1b','Unpaid'],
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:500">{{ $row['tenant']->full_name }}</div>
                                    <div style="font-size:11px;color:#8a8880">{{ $row['tenant']->phone }}</div>
                                </td>
                                <td>{{ $row['unit']->name }}</td>
                                <td class="dep-hide">{{ $row['property']->name }}</td>
                                <td class="dep-hide">{{ $row['lease']->move_in_date?->format('d M Y') ?? '—' }}</td>
                                <td>{{ currency($row['required']) }}</td>
                                <td style="color:#1a6b52;font-weight:500">{{ currency($row['paid_on_lease']) }}</td>
                                <td style="color:{{ $row['outstanding'] > 0 ? '#b91c1c' : '#8a8880' }}">
                                    {{ currency($row['outstanding']) }}
                                </td>
                                <td>
                                    <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $badge[0] }};color:{{ $badge[1] }}">
                                        {{ $badge[2] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Deposit payment transactions --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid rgba(0,0,0,0.07);font-size:13px;font-weight:500">
            Deposit payment transactions
        </div>
        @if($depositPayments->isEmpty())
            <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px">
                No deposit payments recorded yet.
                <a href="{{ route('payments.create') }}" style="color:#1a6b52;font-weight:500">Record one</a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="dep-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th class="dep-hide">Unit</th>
                            <th class="dep-hide">Method</th>
                            <th class="dep-hide">Reference</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depositPayments as $p)
                            <tr>
                                <td>{{ $p->payment_date->format('d M Y') }}</td>
                                <td>{{ $p->tenant?->full_name ?? '—' }}</td>
                                <td class="dep-hide">{{ $p->lease?->unit?->name ?? '—' }}</td>
                                <td class="dep-hide">{{ strtoupper($p->method) }}</td>
                                <td class="dep-hide" style="font-family:monospace;font-size:12px">{{ $p->reference ?? '—' }}</td>
                                <td style="font-weight:500;color:#1a6b52">{{ currency($p->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
</x-layouts.app>