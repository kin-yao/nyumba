<x-layouts.app>
<style>
.pay-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.pay-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
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
    min-width: 620px;
}

/* Mobile payment cards */
.pay-cards { display: none; }
.pay-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.pay-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.pay-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}
.pay-tag {
    font-size: 11px;
    color: #8a8880;
    background: #f5f4f0;
    padding: 2px 8px;
    border-radius: 20px;
}

@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .pay-cards   { display: block; }
}
</style>

<div class="pay-wrap">

    <div class="pay-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Payments</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">{{ $payments->count() }} total transactions</div>
        </div>
        <a href="{{ route('payments.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;flex-shrink:0">
            + Record payment
        </a>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    @if($unallocated->count() > 0)
        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#92400e">
            ⚠ {{ $unallocated->count() }} payment(s) could not be matched to an invoice.
        </div>
    @endif

    @if($payments->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">💳</div>
            <div style="font-weight:500;margin-bottom:4px">No payments yet</div>
            <div>Record your first payment to get started</div>
        </div>
    @else

        @php
            $methodColors = [
                'mpesa'  => ['#dcfce7','#166534'],
                'cash'   => ['#f3f4f6','#4b5563'],
                'bank'   => ['#dbeafe','#1e40af'],
                'cheque' => ['#fef3c7','#92400e'],
            ];
        @endphp

        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Method</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Reference</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Amount</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        @php $mc = $methodColors[$payment->method] ?? $methodColors['cash']; @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td style="padding:11px 14px">
                                @if($payment->tenant)
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:26px;height:26px;border-radius:50%;background:#e6f2ed;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                            {{ strtoupper(substr($payment->tenant->first_name,0,1).substr($payment->tenant->last_name,0,1)) }}
                                        </div>
                                        <span style="font-size:13px">{{ $payment->tenant->full_name }}</span>
                                    </div>
                                @else
                                    <span style="color:#8a8880;font-size:13px">Unknown</span>
                                @endif
                            </td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $payment->lease?->unit?->property?->name ?? '-' }}</td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $mc[0] }};color:{{ $mc[1] }}">
                                    {{ strtoupper($payment->method) }}
                                </span>
                            </td>
                            <td style="padding:11px 14px;font-size:12px;font-family:monospace;color:#8a8880">{{ $payment->reference ?? '-' }}</td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right;color:#15803d">{{ currency($payment->amount) }}</td>
                            <td style="padding:11px 14px">
                                @if($payment->is_allocated)
                                    <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#dcfce7;color:#166534">Allocated</span>
                                @else
                                    <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Unallocated</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="pay-cards">
            @foreach($payments as $payment)
                @php $mc = $methodColors[$payment->method] ?? $methodColors['cash']; @endphp
                <div class="pay-card">
                    <div class="pay-card-top">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0">
                            <div style="width:34px;height:34px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                {{ $payment->tenant ? strtoupper(substr($payment->tenant->first_name,0,1).substr($payment->tenant->last_name,0,1)) : '?' }}
                            </div>
                            <div style="min-width:0">
                                <div style="font-weight:500;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $payment->tenant?->full_name ?? 'Unknown' }}
                                </div>
                                <div style="font-size:11px;color:#8a8880">{{ $payment->payment_date->format('d M Y') }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:15px;font-weight:600;color:#15803d">{{ currency($payment->amount) }}</div>
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;background:{{ $mc[0] }};color:{{ $mc[1] }};margin-top:3px">
                                {{ strtoupper($payment->method) }}
                            </span>
                        </div>
                    </div>
                    <div class="pay-card-meta">
                        @if($payment->lease?->unit?->property)
                            <span class="pay-tag">{{ $payment->lease->unit->property->name }}</span>
                        @endif
                        @if($payment->reference)
                            <span class="pay-tag" style="font-family:monospace">{{ $payment->reference }}</span>
                        @endif
                        @if($payment->is_allocated)
                            <span style="font-size:11px;color:#166534;background:#dcfce7;padding:2px 8px;border-radius:20px">Allocated</span>
                        @else
                            <span style="font-size:11px;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:20px">Unallocated</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>