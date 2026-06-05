<x-layouts.app>
<style>
.tencreate-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.tencreate-section {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 20px;
    max-width: 640px;
    margin-bottom: 14px;
}

.tencreate-section-title {
    font-size: 10px;
    font-weight: 500;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #8a8880;
    margin-bottom: 14px;
}

.form-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-3col {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
}

.field label {
    display: block;
    font-size: 10px;
    font-weight: 500;
    color: #8a8880;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.field input,
.field select {
    width: 100%;
    height: 36px;
    padding: 0 11px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 7px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
}

@media (max-width: 600px) {
    .form-2col { grid-template-columns: 1fr; }
    .form-3col { grid-template-columns: 1fr; }
}
</style>

<div class="tencreate-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('tenants.index') }}" style="color:#8a8880;text-decoration:none">Tenants</a>
            &rsaquo; Move in
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Move in a tenant</div>
    </div>

    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            <strong>Please fix the following errors:</strong>
            <ul style="margin-top:6px;padding-left:16px">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenants.store') }}">
        @csrf

        {{-- Personal details --}}
        <div class="tencreate-section">
            <div class="tencreate-section-title">Personal details</div>
            <div class="form-2col">
                <div class="field">
                    <label>First name</label>
                    <input name="first_name" type="text" required value="{{ old('first_name') }}">
                </div>
                <div class="field">
                    <label>Last name</label>
                    <input name="last_name" type="text" required value="{{ old('last_name') }}">
                </div>
                <div class="field">
                    <label>Phone number</label>
                    <input name="phone" type="text" required value="{{ old('phone') }}" placeholder="07XX or 01XX">
                </div>
                <div class="field">
                    <label>Alternative phone</label>
                    <input name="alt_phone" type="text" value="{{ old('alt_phone') }}" placeholder="Optional">
                </div>
                <div class="field">
                    <label>National ID or passport</label>
                    <input name="id_number" type="text" value="{{ old('id_number') }}">
                </div>
                <div class="field">
                    <label>Email address</label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Optional">
                </div>
            </div>
        </div>

        {{-- Unit assignment --}}
        <div class="tencreate-section">
            <div class="tencreate-section-title">Unit assignment</div>
            <div style="margin-bottom:13px" class="field">
                <label>Select unit</label>
                <select name="unit_id" required id="unit-select" onchange="fillRent(this)">
                    <option value="" disabled selected>Select a vacant unit</option>
                    @foreach($properties as $property)
                        @if($property->units->isNotEmpty())
                            <optgroup label="{{ $property->name }}">
                                @foreach($property->units as $unit)
                                    <option value="{{ $unit->id }}"
                                            data-rent="{{ $unit->rent_amount }}"
                                            data-deposit="{{ $unit->deposit_amount }}"
                                            {{ old('unit_id')==$unit->id?'selected':'' }}>
                                        {{ $unit->name }} &ndash; {{ $unit->type }} &ndash; {{ currency($unit->rent_amount) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-2col">
                <div class="field">
                    <label>Move-in date</label>
                    <input name="move_in_date" type="date" required value="{{ old('move_in_date', date('Y-m-d')) }}">
                </div>
                <div class="field">
                    <label>Lease end date</label>
                    <input name="lease_end_date" type="date" value="{{ old('lease_end_date') }}">
                    <div style="font-size:11px;color:#8a8880;margin-top:3px">Leave blank for open-ended tenancy</div>
                </div>
                <div class="field">
                    <label>Monthly rent ({{ currency_symbol() }})</label>
                    <input name="monthly_rent" type="number" required id="monthly-rent" value="{{ old('monthly_rent') }}">
                </div>
                <div class="field">
                    <label>Annual escalation % (commercial)</label>
                    <input name="escalation_percentage" type="number" step="0.01" value="{{ old('escalation_percentage') }}" placeholder="e.g. 10">
                </div>
            </div>
        </div>

        {{-- Deposit --}}
        <div class="tencreate-section">
            <div class="tencreate-section-title">Deposit</div>
            <div class="form-3col">
                <div class="field">
                    <label>Deposit required ({{ currency_symbol() }})</label>
                    <input name="deposit_required" type="number" required id="deposit-required" value="{{ old('deposit_required') }}">
                </div>
                <div class="field">
                    <label>Deposit paid ({{ currency_symbol() }})</label>
                    <input name="deposit_paid" type="number" required value="{{ old('deposit_paid', '0') }}">
                </div>
                <div class="field">
                    <label>Payment method</label>
                    <select name="deposit_method" required>
                        <option value="" disabled selected>Select method</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="tencreate-section">
            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
            <textarea name="notes" rows="3" placeholder="Any special arrangements or notes..."
                      style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Confirm move-in
            </button>
            <a href="{{ route('tenants.index') }}"
               style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function fillRent(select) {
    var option = select.options[select.selectedIndex];
    var rent    = option.getAttribute('data-rent');
    var deposit = option.getAttribute('data-deposit');
    if (rent)    document.getElementById('monthly-rent').value      = rent;
    if (deposit) document.getElementById('deposit-required').value  = deposit;
}
</script>
</x-layouts.app>