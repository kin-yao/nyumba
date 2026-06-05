<x-layouts.app>
<style>
.report-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

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
    min-width: 520px;
}

.out-cards { display: none; }
.out-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    border-left: 3px solid #b91c1c;
    padding: 14px 16px;
    margin-bottom: 8px;
}
.out-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.out-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 11px;
    color: #8a8880;
}

@media (max-width: 640px) {
    .tbl-scroll { display: none; }
    .out-cards  { display: block; }
}
</style>

<div class="report-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
            &rsaquo; Outstanding balances
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Outstanding balances</div>
    </div>

    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:16px;font-size:13px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">
        Total outstanding: <strong>{{ currency($totalOutstanding) }}</strong>
        across <strong>{{ $leases->count() }} {{ Str::plural('tenant', $leases->count()) }}</strong>
    </div>

    @if($leases->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">✅</div>
            <div style="font-weight:500">All tenants are up to date</div>
        </div>
    @else
        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Phone</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Balance</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Last paid</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Days since</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leases as $item)
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:26px;height:26px;border-radius:50%;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#b91c1c;flex-shrink:0">
                                        {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                                    </div>
                                    <span style="font-size:13px;font-weight:500">{{ $item['tenant']->full_name }}</span>
                                </div>
                            </td>
                            <td style="padding:11px 14px;font-size:13px">{{ $item['unit']->name }}</td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $item['property']->name }}</td>
                            <td style="padding:11px 14px;font-size:12px;font-family:monospace;color:#8a8880">{{ $item['tenant']->phone }}</td>
                            <td style="padding:11px 14px;text-align:right;font-family:'DM Serif Display',serif;font-size:16px;font-weight:600;color:#b91c1c">{{ currency($item['balance']) }}</td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">
                                {{ $item['last_payment'] ? $item['last_payment']->payment_date->format('d M Y') : 'Never paid' }}
                            </td>
                            <td style="padding:11px 14px;font-size:13px;color:{{ ($item['days_since']??0)>30?'#b91c1c':'#8a8880' }}">
                                {{ $item['days_since'] !== null ? $item['days_since'].' days' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="out-cards">
            @foreach($leases as $item)
                <div class="out-card">
                    <div class="out-card-top">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0">
                            <div style="width:32px;height:32px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#b91c1c;flex-shrink:0">
                                {{ strtoupper(substr($item['tenant']->first_name,0,1).substr($item['tenant']->last_name,0,1)) }}
                            </div>
                            <div style="min-width:0">
                                <div style="font-weight:500;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item['tenant']->full_name }}</div>
                                <div style="font-size:11px;color:#8a8880">{{ $item['tenant']->phone }}</div>
                            </div>
                        </div>
                        <div style="font-family:'DM Serif Display',serif;font-size:18px;color:#b91c1c;flex-shrink:0">{{ currency($item['balance']) }}</div>
                    </div>
                    <div class="out-card-meta">
                        <span>Unit {{ $item['unit']->name }}</span>
                        <span>&middot;</span>
                        <span>{{ $item['property']->name }}</span>
                        @if($item['last_payment'])
                            <span>&middot;</span>
                            <span>Last paid {{ $item['last_payment']->payment_date->format('d M Y') }}</span>
                        @else
                            <span>&middot;</span>
                            <span style="color:#b91c1c">Never paid</span>
                        @endif
                        @if($item['days_since'] !== null)
                            <span>&middot;</span>
                            <span style="color:{{ $item['days_since']>30?'#b91c1c':'#8a8880' }}">{{ $item['days_since'] }} days ago</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>