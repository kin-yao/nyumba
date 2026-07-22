<x-layouts.portal>
<style>
.ledger-details summary { list-style: none; cursor: pointer; }
.ledger-details summary::-webkit-details-marker { display: none; }
.ledger-details[open] .ledger-chevron { transform: rotate(180deg); }
</style>
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
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px;color:#8a8880;font-size:13px;text-align:center">
        Contact your landlord for payment details.
    </div>

    {{-- Deposit --}}
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Security deposit</div>
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px;margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:7px">
            <span style="color:#8a8880">Required</span>
            <span style="font-weight:500">{{ currency($depositRequired) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
            <span style="color:#8a8880">Paid</span>
            <span style="font-weight:500;color:{{ $depositPaid >= $depositRequired ? '#15803d' : '#b91c1c' }}">{{ currency($depositPaid) }}</span>
        </div>
    </div>

    {{-- Proof of payment --}}
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Submit proof of payment</div>
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:14px">Made a payment already? Paste the confirmation message below and your landlord will verify it.</div>
        <form method="POST" action="{{ route('portal.payment.proof') }}">
            @csrf
            <div style="margin-bottom:12px">
                <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">This payment is for</label>
                <select name="payment_for" id="proof-payment-for" required onchange="document.getElementById('proof-period-wrap').style.display = this.value === 'rent' ? 'flex' : 'none'"
                        style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <option value="rent">Rent + utilities</option>
                    <option value="deposit">Security deposit</option>
                </select>
            </div>
            <div id="proof-period-wrap" style="display:flex;gap:8px;margin-bottom:12px">
                <div style="flex:1">
                    <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Month</label>
                    <select name="period_month"
                            style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $name)
                            <option value="{{ $i+1 }}" {{ now()->month == $i+1 ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:110px">
                    <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Year</label>
                    <select name="period_year"
                            style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        @for($y = now()->year; $y >= now()->year - 1; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div style="margin-bottom:12px">
                <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">How did you pay?</label>
                <select name="method" required
                        style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <option value="mpesa">M-Pesa</option>
                    <option value="bank">Bank transfer</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Paste the confirmation message</label>
                <textarea name="message" required rows="4" placeholder="e.g. QGH7X8YABC Confirmed. Ksh5,000.00 sent to..."
                          style="width:100%;padding:10px 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical;box-sizing:border-box">{{ old('message') }}</textarea>
            </div>
            <button type="submit"
                    style="width:100%;padding:11px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                Submit for verification
            </button>
        </form>
    </div>

    @if($proofs->isNotEmpty())
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Your submitted proofs</div>
        @php
            $proofStatusMap = ['pending' => ['#fef3c7','#92400e','Awaiting verification'], 'verified' => ['#dcfce7','#166534','Verified'], 'dismissed' => ['#fee2e2','#991b1b','Dismissed']];
        @endphp
        @foreach($proofs as $proof)
            @php $sc = $proofStatusMap[$proof->status] ?? $proofStatusMap['pending']; @endphp
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 16px;margin-bottom:8px">
                <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:6px">
                    <span style="font-size:12px;font-weight:500">{{ ucfirst($proof->payment_for) }}{{ $proof->method ? ' · '.strtoupper($proof->method) : '' }}</span>
                    <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:{{ $sc[0] }};color:{{ $sc[1] }}">{{ $sc[2] }}</span>
                </div>
                <div style="font-size:11px;color:#8a8880;margin-bottom:6px">
                    {{ $proof->created_at->format('d M Y, g:ia') }}
                    @if($proof->periodLabel())
                        &middot; For {{ $proof->periodLabel() }}
                    @endif
                </div>
                <div style="font-size:12px;color:#111110;background:#f5f4f0;border-radius:6px;padding:8px 10px;white-space:pre-wrap">{{ $proof->message }}</div>
            </div>
        @endforeach
    @endif

    {{-- Transaction ledger, grouped by month, collapsible --}}
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;margin-top:6px">Transaction ledger</div>
    @if($ledgerByMonth->isEmpty())
        <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:24px;text-align:center;color:#8a8880;font-size:13px;margin-bottom:14px">
            No transactions yet.
        </div>
    @else
        @foreach($ledgerByMonth as $monthLabel => $rows)
            @php
                $monthCharged = collect($rows)->sum('charged');
                $monthPaid    = collect($rows)->sum('paid');
            @endphp
            <details class="ledger-details" style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);margin-bottom:10px;overflow:hidden">
                <summary style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;background:#faf9f7">
                    <span style="font-size:12.5px;font-weight:600">{{ $monthLabel }}</span>
                    <span style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:11.5px;white-space:nowrap">
                            @if($monthCharged > 0)<span style="color:#b91c1c">-{{ currency($monthCharged) }}</span>@endif
                            @if($monthPaid > 0)<span style="color:#15803d;margin-left:6px">+{{ currency($monthPaid) }}</span>@endif
                        </span>
                        <span class="ledger-chevron" style="font-size:11px;color:#8a8880;transition:transform .15s;display:inline-block">&#9662;</span>
                    </span>
                </summary>
                @foreach($rows as $row)
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 16px;border-top:1px solid rgba(0,0,0,0.04)">
                        <div style="min-width:0">
                            <div style="font-size:12.5px">{{ $row['description'] }}</div>
                            <div style="font-size:10.5px;color:#8a8880;margin-top:1px">{{ $row['date']->format('d M') }}{{ $row['reference'] ? ' · ' . $row['reference'] : '' }}</div>
                        </div>
                        @if($row['charged'] !== null)
                            <div style="font-size:13px;font-weight:600;color:#b91c1c;flex-shrink:0">-{{ currency($row['charged']) }}</div>
                        @else
                            <div style="font-size:13px;font-weight:600;color:#15803d;flex-shrink:0">+{{ currency($row['paid']) }}</div>
                        @endif
                    </div>
                @endforeach
            </details>
        @endforeach
    @endif
@endif
</x-layouts.portal>