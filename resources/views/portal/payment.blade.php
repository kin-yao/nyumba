<x-layouts.portal>
@if(!$lease)
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:30px;text-align:center;color:#8a8880;font-size:13px">
        We couldn't find an active tenancy on your account. Please contact your landlord.
    </div>
@else
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px;text-align:center">
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Current balance</div>
        <div style="font-family:'DM Serif Display',serif;font-size:32px;color:{{ $balance > 0 ? '#b91c1c' : ($balance < 0 ? '#1a6b52' : '#111110') }}">
            {{ currency(abs($balance)) }}
        </div>
        <div style="font-size:12px;color:#8a8880;margin-top:3px">
            @if($balance > 0) Amount due
            @elseif($balance < 0) You're in credit
            @else Fully paid up
            @endif
        </div>
    </div>

    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">How to pay</div>

    @if($property->hasPaymentConfig())
        <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px">
            @if($property->payment_type === 'paybill')
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.06)">
                    <span style="color:#8a8880">M-Pesa Paybill</span>
                    <span style="font-weight:700;font-family:monospace;font-size:15px">{{ $property->business_number }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:#8a8880">Account number</span>
                    <span style="font-weight:600">
                        @if($property->account_format === 'unit_number') {{ $unit->name }}
                        @elseif($property->account_format === 'tenant_name') {{ $tenant->full_name }}
                        @elseif($property->account_format === 'phone_number') {{ $tenant->phone }}
                        @else {{ $unit->name }}
                        @endif
                    </span>
                </div>
            @else
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:#8a8880">M-Pesa Till Number</span>
                    <span style="font-weight:700;font-family:monospace;font-size:15px">{{ $property->till_number }}</span>
                </div>
            @endif
        </div>
        <div style="font-size:12px;color:#8a8880;margin-bottom:20px;line-height:1.6">
            Go to M-Pesa &rarr; Lipa na M-Pesa &rarr; {{ $property->payment_type === 'paybill' ? 'Pay Bill' : 'Buy Goods and Services' }},
            enter the details above, and the amount you're paying.
        </div>
    @else
        <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px;color:#8a8880;font-size:13px;text-align:center">
            Payment details haven't been set up yet. Please contact your landlord for how to pay.
        </div>
    @endif

    {{-- Deposit --}}
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Security deposit</div>
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:7px">
            <span style="color:#8a8880">Required</span>
            <span style="font-weight:500">{{ currency($depositRequired) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
            <span style="color:#8a8880">Paid</span>
            <span style="font-weight:500;color:{{ $depositPaid >= $depositRequired ? '#15803d' : '#b91c1c' }}">{{ currency($depositPaid) }}</span>
        </div>
    </div>
@endif
</x-layouts.portal>