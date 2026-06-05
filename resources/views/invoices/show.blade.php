<x-layouts.app>
<style>
.invshow-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.invshow-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}

.invshow-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 16px;
}

@media (max-width: 800px) {
    .invshow-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="invshow-wrap">

    <div class="invshow-header">
        <div style="font-size:12px;color:#8a8880">
            <a href="{{ route('invoices.index') }}" style="color:#8a8880;text-decoration:none">Invoices</a>
            &rsaquo; {{ $invoice->reference }}
        </div>
        <a href="{{ route('invoices.pdf', $invoice) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap">
            Download PDF
        </a>
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

    <div class="invshow-layout">

        {{-- Invoice card --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden">
            <div style="padding:18px 20px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap">
                <div>
                    <div style="font-size:11px;color:#8a8880;margin-bottom:2px">Invoice to</div>
                    <div style="font-weight:500;font-size:14px">{{ $invoice->lease->tenant->full_name }}</div>
                    <div style="font-size:12px;color:#8a8880">
                        Unit {{ $invoice->lease->unit->name }} &middot; {{ $invoice->lease->unit->property->name }}
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-family:monospace;font-size:13px;color:#8a8880">{{ $invoice->reference }}</div>
                    @php
                        $sc = [
                            'paid'    => ['#dcfce7','#166534'],
                            'partial' => ['#fef3c7','#92400e'],
                            'overdue' => ['#fee2e2','#991b1b'],
                            'sent'    => ['#dbeafe','#1e40af'],
                            'draft'   => ['#f3f4f6','#4b5563'],
                        ];
                        $c = $sc[$invoice->status] ?? $sc['draft'];
                    @endphp
                    <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $c[0] }};color:{{ $c[1] }};margin-top:4px">
                        {{ ucfirst($invoice->status) }}
                    </span>
                    <div style="font-size:11px;color:#8a8880;margin-top:4px">
                        Due {{ $invoice->due_date->format('d M Y') }}
                    </div>
                </div>
            </div>

            {{-- Line items --}}
            <div style="padding:14px 20px">
                @foreach($invoice->lineItems as $item)
                    <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid rgba(0,0,0,0.05);gap:10px">
                        <span>{{ $item->description }}</span>
                        <span style="font-weight:500;white-space:nowrap">{{ currency($item->amount) }}</span>
                    </div>
                @endforeach
            </div>

            <div style="padding:12px 20px;border-top:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;font-weight:500;align-items:center">
                <span>Total due</span>
                <span style="font-family:'DM Serif Display',serif;font-size:20px">{{ currency($invoice->total_amount) }}</span>
            </div>

            <div style="padding:10px 20px;border-top:1px solid rgba(0,0,0,0.07);font-size:12px;color:#8a8880">
                Period: {{ \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->format('F Y') }}
            </div>

            <div style="padding:12px 20px;border-top:1px solid rgba(0,0,0,0.07);display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <a href="{{ route('payments.create') }}"
                   style="display:inline-flex;padding:6px 13px;background:#1a6b52;color:#fff;border-radius:7px;font-size:12px;text-decoration:none;font-weight:500;white-space:nowrap">
                    Record payment
                </a>
                @if($invoice->allocations->isEmpty())
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                          onsubmit="return confirm('Delete invoice {{ $invoice->reference }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="display:inline-flex;padding:6px 13px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.3);border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                            Delete invoice
                        </button>
                    </form>
                @else
                    <span style="font-size:12px;color:#8a8880">Cannot delete — payments recorded</span>
                @endif
            </div>
        </div>

        {{-- Payment history --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
            <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:12px">
                Payment history
            </div>
            @if($invoice->allocations->isEmpty())
                <div style="color:#8a8880;font-size:13px;padding:20px 0;text-align:center">
                    No payments recorded yet for this invoice.
                </div>
            @else
                @foreach($invoice->allocations as $allocation)
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(0,0,0,0.05);font-size:13px;gap:10px">
                        <div>
                            <div>{{ $allocation->payment->payment_date->format('d M Y') }}</div>
                            <div style="font-size:11px;color:#8a8880;font-family:monospace">{{ $allocation->payment->reference }}</div>
                        </div>
                        <div style="font-weight:500;color:#15803d;white-space:nowrap">{{ currency($allocation->amount) }}</div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
</x-layouts.app>