<x-layouts.app>
<style>
.inv-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.inv-band {
    position: relative;
    background: #0e3f30;
    border-radius: 12px;
    overflow: hidden;
    padding: 20px 24px;
    margin-bottom: 20px;y
}
.inv-band-shards { position: absolute; inset: 0; pointer-events: none; }
.inv-band-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.inv-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
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
    min-width: 650px;
}

/* Mobile invoice cards */
.inv-cards { display: none; }
.inv-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
    text-decoration: none;
    color: inherit;
    display: block;
}
.inv-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.inv-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

@media (max-width: 640px) {
    .tbl-scroll { display: none; }
    .inv-cards  { display: block; }
    .inv-band   { padding: 18px; }
}
</style>

<div class="inv-wrap">

    <div class="inv-band">
        <div class="inv-band-shards">
            <svg width="100%" height="100%" viewBox="0 0 1200 120" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <polygon points="-72,0 792,0 480,120 -72,120" fill="#ffffff" opacity="0.04"/>
                <polygon points="96,0 756,0 360,120 -72,120" fill="#ffffff" opacity="0.05"/>
            </svg>
        </div>
        <div class="inv-band-content">
            <div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1;color:#fff">Invoices</div>
                <div style="font-size:13px;color:rgba(244,242,236,.6);margin-top:3px">{{ $invoices->count() }} total</div>
            </div>
            <div class="inv-actions">
                @php $draftCount = $invoices->where('status', 'draft')->count(); @endphp
                @if($draftCount > 0)
                    <form method="POST" action="{{ route('invoices.send-all') }}"
                          onsubmit="return confirm('Send {{ $draftCount }} draft {{ Str::plural('invoice', $draftCount) }} to tenants via SMS now?')">
                        @csrf
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:6px;padding:8px 15px;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                            Send all ({{ $draftCount }})
                        </button>
                    </form>
                @endif
                <a href="{{ route('invoices.bulk') }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 15px;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:7px;font-size:13px;text-decoration:none;white-space:nowrap">
                    Bulk generate
                </a>
                <a href="{{ route('invoices.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#fff;color:#0e3f30;border:none;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap">
                    + New invoice
                </a>
            </div>
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

    @if($invoices->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">🧾</div>
            <div style="font-weight:500;margin-bottom:4px">No invoices yet</div>
            <div>Create your first invoice or use bulk generate</div>
        </div>
    @else

        @php
            $statusConfig = [
                'paid'    => ['bg'=>'#dcfce7','text'=>'#166534','label'=>'Paid'],
                'partial' => ['bg'=>'#fef3c7','text'=>'#92400e','label'=>'Partial'],
                'overdue' => ['bg'=>'#fee2e2','text'=>'#991b1b','label'=>'Overdue'],
                'sent'    => ['bg'=>'#dbeafe','text'=>'#1e40af','label'=>'Unpaid'],
                'draft'   => ['bg'=>'#f3f4f6','text'=>'#4b5563','label'=>'Draft'],
            ];
        @endphp

        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Ref</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Period</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Amount</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Due</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                        <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        @php $sc = $statusConfig[$invoice->status] ?? $statusConfig['draft']; @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px;font-size:12px;font-family:monospace;color:#8a8880">{{ $invoice->reference }}</td>
                            <td style="padding:11px 14px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:26px;height:26px;border-radius:50%;background:#e6f2ed;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                        {{ strtoupper(substr($invoice->lease->tenant->first_name,0,1).substr($invoice->lease->tenant->last_name,0,1)) }}
                                    </div>
                                    <span style="font-size:13px">{{ $invoice->lease->tenant->full_name }}</span>
                                </div>
                            </td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $invoice->lease->unit->name }}</td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">
                                {{ \Carbon\Carbon::createFromDate($invoice->period_year,$invoice->period_month,1)->format('M Y') }}
                            </td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right">{{ currency($invoice->total_amount) }}</td>
                            <td style="padding:11px 14px;font-size:13px;color:{{ $invoice->isOverdue()?'#b91c1c':'#8a8880' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td style="padding:11px 14px;text-align:right">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                   style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:6px;font-size:12px;text-decoration:none">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="inv-cards">
            @foreach($invoices as $invoice)
                @php $sc = $statusConfig[$invoice->status] ?? $statusConfig['draft']; @endphp
                <a href="{{ route('invoices.show', $invoice) }}" class="inv-card">
                    <div class="inv-card-top">
                        <div style="min-width:0">
                            <div style="font-weight:500;font-size:13px;margin-bottom:2px">{{ $invoice->lease->tenant->full_name }}</div>
                            <div style="font-size:11px;color:#8a8880;font-family:monospace">{{ $invoice->reference }}</div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:15px;font-weight:600;color:#111110">{{ currency($invoice->total_amount) }}</div>
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;background:{{ $sc['bg'] }};color:{{ $sc['text'] }};margin-top:3px">
                                {{ $sc['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="inv-card-meta">
                        <span style="font-size:11px;color:#8a8880;background:#f5f4f0;padding:2px 8px;border-radius:20px">
                            Unit {{ $invoice->lease->unit->name }}
                        </span>
                        <span style="font-size:11px;color:#8a8880;background:#f5f4f0;padding:2px 8px;border-radius:20px">
                            {{ \Carbon\Carbon::createFromDate($invoice->period_year,$invoice->period_month,1)->format('M Y') }}
                        </span>
                        <span style="font-size:11px;color:{{ $invoice->isOverdue()?'#b91c1c':'#8a8880' }}">
                            Due {{ $invoice->due_date->format('d M') }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>