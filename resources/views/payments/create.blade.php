<x-layouts.app>
<style>
.paycreate-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.paycreate-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 14px;
    max-width: 900px;
}

.form-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 800px) {
    .paycreate-layout { grid-template-columns: 1fr; }
    .panel-sticky      { position: static !important; }
}

@media (max-width: 480px) {
    .form-2col { grid-template-columns: 1fr; }
}
</style>

<div class="paycreate-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('payments.index') }}" style="color:#8a8880;text-decoration:none">Payments</a>
            &rsaquo; Record payment
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Record a payment</div>
    </div>

    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="paycreate-layout">

        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px">
                <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:14px">Payment details</div>

                <div style="margin-bottom:13px">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Tenant</label>
                    <select name="tenant_id" required onchange="loadTenantInfo(this)"
                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select tenant</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}"
                                    data-balance="{{ $tenant->activeLease?->invoices->sum(fn($i)=>$i->total_amount-$i->amount_paid) }}"
                                    data-unit="{{ $tenant->activeLease?->unit?->name }}"
                                    data-property="{{ $tenant->activeLease?->unit?->property?->name }}"
                                    data-rent="{{ $tenant->activeLease?->monthly_rent }}"
                                    data-deposit-required="{{ $tenant->activeLease?->deposit_required ?? 0 }}"
                                    data-deposit-paid="{{ $tenant->activeLease?->deposit_paid ?? 0 }}"
                                    {{ old('tenant_id')==$tenant->id?'selected':'' }}>
                                {{ $tenant->full_name }} &ndash; {{ $tenant->activeLease?->unit?->name }}, {{ $tenant->activeLease?->unit?->property?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment type --}}
                <div style="margin-bottom:13px">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Payment type</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        @foreach(['rent' => 'Rent / Utilities', 'deposit' => 'Security deposit', 'other' => 'Other'] as $val => $label)
                            <label style="display:flex;align-items:center;gap:6px;padding:6px 13px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;cursor:pointer;font-size:13px;font-family:'DM Sans',sans-serif"
                                   id="type-label-{{ $val }}">
                                <input type="radio" name="payment_type" value="{{ $val }}"
                                       onchange="onTypeChange('{{ $val }}')"
                                       {{ old('payment_type', 'rent') === $val ? 'checked' : '' }}
                                       style="accent-color:#1a6b52">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-2col" style="margin-bottom:13px">
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Amount ({{ currency_symbol() }})</label>
                        <input name="amount" type="number" step="0.01" required min="1" id="payment-amount"
                               value="{{ old('amount') }}" placeholder="e.g. 9500"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Date received</label>
                        <input name="payment_date" type="date" required value="{{ old('payment_date', date('Y-m-d')) }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                </div>

                <div class="form-2col" style="margin-bottom:13px">
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Payment method</label>
                        <select name="method" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select method</option>
                            <option value="mpesa"  {{ old('method')=='mpesa' ?'selected':'' }}>M-Pesa</option>
                            <option value="cash"   {{ old('method')=='cash'  ?'selected':'' }}>Cash</option>
                            <option value="bank"   {{ old('method')=='bank'  ?'selected':'' }}>Bank transfer</option>
                            <option value="cheque" {{ old('method')=='cheque'?'selected':'' }}>Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Reference number</label>
                        <input name="reference" type="text" value="{{ old('reference') }}"
                               placeholder="M-Pesa ref or cheque number"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes..."
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save payment
                </button>
                <a href="{{ route('payments.index') }}"
                   style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none">
                    Cancel
                </a>
            </div>
        </form>

        {{-- Tenant summary panel --}}
        <div id="tenant-panel" style="display:none">
            <div class="panel-sticky" style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;position:sticky;top:20px">
                <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:12px">Tenant summary</div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    <div id="panel-avatar" style="width:34px;height:34px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#1a6b52;flex-shrink:0"></div>
                    <div>
                        <div id="panel-name" style="font-size:13px;font-weight:500"></div>
                        <div id="panel-unit" style="font-size:11px;color:#8a8880"></div>
                    </div>
                </div>
                <div style="display:grid;gap:8px;font-size:12px;margin-bottom:12px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#8a8880">Monthly rent</span>
                        <span id="panel-rent" style="font-weight:500"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#8a8880">Outstanding balance</span>
                        <span id="panel-balance" style="font-weight:500;color:#b91c1c"></span>
                    </div>
                    <div id="panel-deposit-row" style="display:flex;justify-content:space-between">
                        <span style="color:#8a8880">Deposit required</span>
                        <span id="panel-deposit" style="font-weight:500"></span>
                    </div>
                    <div id="panel-deposit-paid-row" style="display:flex;justify-content:space-between">
                        <span style="color:#8a8880">Deposit paid</span>
                        <span id="panel-deposit-paid" style="font-weight:500;color:#1a6b52"></span>
                    </div>
                </div>
                <div id="panel-note" style="padding-top:12px;border-top:1px solid rgba(0,0,0,0.07);font-size:11px;color:#8a8880">
                    Payment will be automatically allocated to the oldest unpaid invoice first.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var currentTenantData = {};

function loadTenantInfo(select) {
    var option   = select.options[select.selectedIndex];
    var name     = option.text.split(' – ')[0];
    var unit     = option.getAttribute('data-unit');
    var property = option.getAttribute('data-property');
    var balance  = parseFloat(option.getAttribute('data-balance') || 0);
    var rent     = parseFloat(option.getAttribute('data-rent') || 0);
    var depReq   = parseFloat(option.getAttribute('data-deposit-required') || 0);
    var depPaid  = parseFloat(option.getAttribute('data-deposit-paid') || 0);

    currentTenantData = { balance: balance, rent: rent, depReq: depReq, depPaid: depPaid };

    document.getElementById('tenant-panel').style.display = 'block';
    document.getElementById('panel-avatar').textContent   = name.split(' ').map(function(n){return n[0];}).join('').substring(0,2).toUpperCase();
    document.getElementById('panel-name').textContent     = name;
    document.getElementById('panel-unit').textContent     = 'Unit ' + unit + ' · ' + property;
    document.getElementById('panel-rent').textContent     = '{{ currency_symbol() }} ' + rent.toLocaleString();
    document.getElementById('panel-balance').textContent  = '{{ currency_symbol() }} ' + balance.toLocaleString();
    document.getElementById('panel-deposit').textContent  = '{{ currency_symbol() }} ' + depReq.toLocaleString();
    document.getElementById('panel-deposit-paid').textContent = '{{ currency_symbol() }} ' + depPaid.toLocaleString();

    onTypeChange(document.querySelector('input[name="payment_type"]:checked')?.value || 'rent');
}

function onTypeChange(type) {
    var note        = document.getElementById('panel-note');
    var amountInput = document.getElementById('payment-amount');
    var depRows     = [document.getElementById('panel-deposit-row'), document.getElementById('panel-deposit-paid-row')];

    if (type === 'deposit') {
        note.textContent = 'Deposit payments are held securely and are not allocated to invoices.';
        depRows.forEach(r => r.style.display = 'flex');
        if (currentTenantData.depReq && !amountInput.value) {
            amountInput.value = Math.max(0, currentTenantData.depReq - currentTenantData.depPaid);
        }
    } else if (type === 'rent') {
        note.textContent = 'Payment will be automatically allocated to the oldest unpaid invoice first.';
        depRows.forEach(r => r.style.display = 'none');
        if (currentTenantData.balance && !amountInput.value) {
            amountInput.value = currentTenantData.balance;
        }
    } else {
        note.textContent = 'Payment will be recorded but not automatically allocated.';
        depRows.forEach(r => r.style.display = 'none');
    }
}
</script>
</x-layouts.app>