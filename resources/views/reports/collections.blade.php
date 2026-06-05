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

.method-grid {
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
    min-width: 460px;
}

.coll-cards { display: none; }
.coll-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 12px 14px;
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

@media (max-width: 700px) {
    .method-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .coll-cards  { display: block; }
}
</style>

<div class="report-wrap">

    <div class="report-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
                &rsaquo; Collection report
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Collection report</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </div>
        </div>
        <form method="GET" action="{{ route('reports.collections') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex-shrink:0">
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

    {{-- Method breakdown --}}
    <div class="method-grid">
        @foreach(['mpesa'=>'M-Pesa','cash'=>'Cash','bank'=>'Bank transfer','cheque'=>'Cheque'] as $key=>$label)
            @php $data = $byMethod->get($key, ['count'=>0,'amount'=>0]); @endphp
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 18px">
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">{{ $label }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(16px,2.5vw,22px)">{{ currency($data['amount']) }}</div>
                <div style="font-size:12px;color:#8a8880;margin-top:3px">{{ $data['count'] }} {{ Str::plural('transaction',$data['count']) }}</div>
            </div>
        @endforeach
    </div>

    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 18px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
        <span style="font-size:13px;color:#8a8880">Total collected</span>
        <span style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,24px);color:#15803d">{{ currency($totalCollected) }}</span>
    </div>

    @if($payments->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:40px;text-align:center;color:#8a8880;font-size:13px">
            No payments recorded for this period.
        </div>
    @else
        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Reference</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Method</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880;white-space:nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td style="padding:11px 14px;font-size:13px">{{ $payment->tenant?->full_name ?? 'Unknown' }}</td>
                            <td style="padding:11px 14px;font-size:12px;font-family:monospace;color:#8a8880">{{ $payment->reference ?? '-' }}</td>
                            <td style="padding:11px 14px;font-size:12px;text-transform:uppercase;color:#8a8880">{{ $payment->method }}</td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right;color:#15803d">{{ currency($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="coll-cards">
            @foreach($payments as $payment)
                <div class="coll-card">
                    <div style="min-width:0">
                        <div style="font-weight:500;font-size:13px;margin-bottom:2px">{{ $payment->tenant?->full_name ?? 'Unknown' }}</div>
                        <div style="font-size:11px;color:#8a8880">
                            {{ $payment->payment_date->format('d M Y') }}
                            &middot; {{ strtoupper($payment->method) }}
                            @if($payment->reference) &middot; <span style="font-family:monospace">{{ $payment->reference }}</span> @endif
                        </div>
                    </div>
                    <div style="font-size:14px;font-weight:600;color:#15803d;flex-shrink:0">{{ currency($payment->amount) }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>