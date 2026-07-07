<x-layouts.app>
<style>
.pay-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.pay-band {
    position: relative;
    background: #0e3f30;
    border-radius: 12px;
    overflow: hidden;
    padding: 20px 24px;
    margin-bottom: 20px;
}
.pay-band-shards { position: absolute; inset: 0; pointer-events: none; }
.pay-band-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.pay-tabs {
    display: flex;
    gap: 4px;
    background: #f5f4f0;
    border-radius: 8px;
    padding: 3px;
    margin-bottom: 20px;
    width: fit-content;
}
.pay-tab {
    padding: 6px 16px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    color: #8a8880;
    transition: all .15s;
    position: relative;
}
.pay-tab.active {
    background: #fff;
    color: #111110;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.pay-tab .badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #b91c1c;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    border-radius: 10px;
    padding: 1px 5px;
    margin-left: 5px;
    min-width: 16px;
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
    .tbl-scroll { display: none; }
    .pay-cards  { display: block; }
    .pay-band   { padding: 18px; }
}
</style>

<div class="pay-wrap">

    <div class="pay-band">
        <div class="pay-band-shards">
            <svg width="100%" height="100%" viewBox="0 0 1200 120" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <polygon points="-72,0 792,0 480,120 -72,120" fill="#ffffff" opacity="0.04"/>
                <polygon points="96,0 756,0 360,120 -72,120" fill="#ffffff" opacity="0.05"/>
            </svg>
        </div>
        <div class="pay-band-content">
            <div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1;color:#fff">Payments</div>
                <div style="font-size:13px;color:rgba(244,242,236,.6);margin-top:3px">{{ $payments->count() }} total</div>
            </div>
            <a href="{{ route('payments.create') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#fff;color:#0e3f30;border:none;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;flex-shrink:0">
                + Record payment
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            {{ session('error') }}
        </div>
    @endif

    <div class="pay-tabs">
        <button class="pay-tab active" onclick="showTab('all', this)">
            All payments
        </button>
        <button class="pay-tab" onclick="showTab('unmatched', this)">
            Unmatched M-Pesa
            @if($unmatched->count() > 0)
                <span class="badge-count">{{ $unmatched->count() }}</span>
            @endif
        </button>
    </div>

    {{-- All payments --}}
    <div id="tab-all">
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
                                        <span style="color:#8a8880;font-size:13px">Unmatched</span>
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
                                    @if($payment->payment_type === 'deposit')
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Deposit held</span>
                                    @elseif($payment->is_allocated)
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#dcfce7;color:#166534">Allocated</span>
                                    @elseif(!$payment->tenant_id)
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fee2e2;color:#991b1b">Unmatched</span>
                                    @else
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Unallocated</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                                        {{ $payment->tenant?->full_name ?? 'Unmatched' }}
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
                            @if($payment->payment_type === 'deposit')
                                <span style="font-size:11px;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:20px">Deposit held</span>
                            @elseif($payment->is_allocated)
                                <span style="font-size:11px;color:#166534;background:#dcfce7;padding:2px 8px;border-radius:20px">Allocated</span>
                            @elseif(!$payment->tenant_id)
                                <span style="font-size:11px;color:#991b1b;background:#fee2e2;padding:2px 8px;border-radius:20px">Unmatched</span>
                            @else
                                <span style="font-size:11px;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:20px">Unallocated</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Unmatched M-Pesa --}}
    <div id="tab-unmatched" style="display:none">
        @if($unmatched->isEmpty())
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
                <div style="font-size:36px;margin-bottom:12px">✅</div>
                <div style="font-weight:500;margin-bottom:4px">No unmatched payments</div>
                <div>All M-Pesa payments have been matched</div>
            </div>
        @else
            <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:12px 15px;margin-bottom:16px;font-size:13px;color:#92400e">
                ⚠ {{ $unmatched->count() }} payment(s) could not be automatically matched. Assign each one to the correct tenant below.
            </div>

            <div style="display:grid;gap:12px">
                @foreach($unmatched as $payment)
                    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:12px">
                            <div>
                                <div style="font-size:15px;font-weight:600;color:#15803d">{{ currency($payment->amount) }}</div>
                                <div style="font-size:12px;color:#8a8880;margin-top:2px">
                                    {{ $payment->payment_date->format('d M Y') }}
                                    @if($payment->reference)
                                        &middot; <span style="font-family:monospace">{{ $payment->reference }}</span>
                                    @endif
                                </div>
                            </div>
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fee2e2;color:#991b1b">
                                Unmatched
                            </span>
                        </div>

                        @if($payment->notes)
                            <div style="font-size:12px;color:#8a8880;background:#f5f4f0;border-radius:6px;padding:8px 10px;margin-bottom:12px;line-height:1.5">
                                {{ $payment->notes }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('payments.assign', $payment) }}"
                              style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
                            @csrf
                            <div style="flex:1;min-width:200px">
                                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Assign to tenant</label>
                                <select name="tenant_id" required
                                        style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                    <option value="">Select tenant...</option>
                                    @foreach($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">
                                            {{ $tenant->full_name }}
                                            @if($tenant->activeLease?->unit)
                                                — {{ $tenant->activeLease->unit->name }}, {{ $tenant->activeLease->unit->property->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                    style="height:36px;padding:0 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                                Assign
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function showTab(name, el) {
    document.getElementById('tab-all').style.display       = name === 'all'       ? 'block' : 'none';
    document.getElementById('tab-unmatched').style.display = name === 'unmatched' ? 'block' : 'none';
    document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

if (window.location.hash === '#unmatched') {
    showTab('unmatched', document.querySelectorAll('.pay-tab')[1]);
}
</script>
</x-layouts.app>