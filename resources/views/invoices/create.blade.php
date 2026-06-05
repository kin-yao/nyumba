<x-layouts.app>
<style>
.invcreate-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.invcreate-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 14px;
}

.line-item-row {
    display: grid;
    grid-template-columns: 1fr 110px 110px 32px;
    align-items: center;
    border-bottom: 1px solid rgba(0,0,0,0.07);
}

.line-item-header {
    display: grid;
    grid-template-columns: 1fr 110px 110px 32px;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    background: #faf9f7;
}

.period-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.form-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 800px) {
    .invcreate-layout {
        grid-template-columns: 1fr;
    }
    .summary-sticky {
        position: static !important;
    }
}

@media (max-width: 560px) {
    .form-2col { grid-template-columns: 1fr; }
    .line-item-row,
    .line-item-header {
        grid-template-columns: 1fr 80px 80px 28px;
    }
}
</style>

<div class="invcreate-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('invoices.index') }}" style="color:#8a8880;text-decoration:none">Invoices</a>
            &rsaquo; New invoice
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Create invoice</div>
    </div>

    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}" id="invoice-form">
        @csrf
        <div class="invcreate-layout">

            {{-- Left --}}
            <div>
                {{-- Invoice details --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:14px">Invoice details</div>

                    <div style="margin-bottom:13px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Tenant</label>
                        <select name="lease_id" required id="lease-select" onchange="onLeaseChange(this)"
                                style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select tenant</option>
                            @foreach($leases as $lease)
                                <option value="{{ $lease->id }}"
                                        data-rent="{{ $lease->monthly_rent }}"
                                        data-invoiced-months="{{ $lease->invoices->map(fn($i)=>$i->period_month.'-'.$i->period_year)->implode(',') }}"
                                        {{ old('lease_id')==$lease->id?'selected':'' }}>
                                    {{ $lease->tenant->full_name }} &ndash; {{ $lease->unit->name }}, {{ $lease->unit->property->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:13px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Period</label>
                        <div class="period-grid">
                            <select name="period_month" required id="period-month" onchange="checkDuplicate();loadUtilityCharges()"
                                    style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ (old('period_month',now()->month)==$m)?'selected':'' }}>
                                        {{ \Carbon\Carbon::createFromDate(now()->year,$m,1)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="period_year" type="number" required id="period-year"
                                   value="{{ old('period_year', now()->year) }}"
                                   onchange="checkDuplicate();loadUtilityCharges()"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div id="duplicate-warning" style="display:none;margin-top:5px;font-size:11px;color:#b91c1c;font-weight:500"></div>
                    </div>

                    <div class="form-2col">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Invoice date</label>
                            <input name="invoice_date" type="date" required value="{{ old('invoice_date', date('Y-m-d')) }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Due date</label>
                            <input name="due_date" type="date" required value="{{ old('due_date', date('Y-m-d', strtotime('+10 days'))) }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                    </div>
                </div>

                {{-- Line items --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                        <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880">Line items</div>
                        <div id="utility-loader" style="display:none;font-size:11px;color:#1a6b52">Loading utility charges...</div>
                    </div>

                    <div style="border:1px solid rgba(0,0,0,0.07);border-radius:8px;overflow:hidden;margin-bottom:10px">
                        <div class="line-item-header">
                            <div style="padding:7px 12px;font-size:10px;letter-spacing:.05em;text-transform:uppercase;color:#8a8880;font-weight:500">Description</div>
                            <div style="padding:7px 12px;font-size:10px;letter-spacing:.05em;text-transform:uppercase;color:#8a8880;font-weight:500">Type</div>
                            <div style="padding:7px 12px;font-size:10px;letter-spacing:.05em;text-transform:uppercase;color:#8a8880;font-weight:500">Amt ({{ currency_symbol() }})</div>
                            <div></div>
                        </div>
                        <div id="line-items">
                            <div class="line-item-row">
                                <div style="padding:7px 10px">
                                    <input type="text" name="descriptions[]" required id="first-desc"
                                           placeholder="e.g. June 2025 rent"
                                           style="width:100%;border:none;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:transparent">
                                </div>
                                <div style="padding:7px 6px">
                                    <select name="types[]" style="width:100%;border:none;font-size:12px;font-family:'DM Sans',sans-serif;outline:none;background:transparent">
                                        <option value="rent">Rent</option>
                                        <option value="water">Water</option>
                                        <option value="electricity">Electricity</option>
                                        <option value="garbage">Garbage</option>
                                        <option value="service_charge">Service</option>
                                        <option value="security_levy">Security</option>
                                        <option value="penalty">Penalty</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div style="padding:7px 6px">
                                    <input type="number" name="amounts[]" required min="0" id="first-amount"
                                           oninput="updateTotal()"
                                           style="width:100%;border:none;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:transparent">
                                </div>
                                <div></div>
                            </div>
                        </div>
                        <div style="padding:9px 12px">
                            <button type="button" onclick="addLineItem()"
                                    style="font-size:12px;color:#1a6b52;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif">
                                + Add line item
                            </button>
                        </div>
                    </div>

                    <div style="font-size:11px;color:#8a8880">
                        Utility charges for the selected period load automatically from meter readings.
                    </div>
                </div>
            </div>

            {{-- Right: summary --}}
            <div>
                <div class="summary-sticky" style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;position:sticky;top:20px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:12px">Summary</div>
                    <div id="summary-items" style="font-size:13px;display:grid;gap:7px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
                        <div style="color:#8a8880;font-size:12px">Select a tenant to see line items</div>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:500;margin-bottom:20px">
                        <span>Total</span>
                        <span style="font-family:'DM Serif Display',serif;font-size:18px" id="total-display">{{ currency_symbol() }} 0</span>
                    </div>
                    <button type="submit" id="submit-btn"
                            style="width:100%;padding:8px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Save invoice
                    </button>
                    <a href="{{ route('invoices.index') }}"
                       style="display:block;text-align:center;margin-top:8px;font-size:13px;color:#8a8880;text-decoration:none">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function onLeaseChange(select) {
    var rent = parseFloat(select.options[select.selectedIndex].getAttribute('data-rent')) || 0;
    var month = document.getElementById('period-month');
    var monthName = month.options[month.selectedIndex].text;
    var year = document.getElementById('period-year').value;
    document.getElementById('first-desc').value = monthName + ' ' + year + ' rent';
    document.getElementById('first-amount').value = rent;
    checkDuplicate();
    loadUtilityCharges();
    updateTotal();
}

function checkDuplicate() {
    var select = document.getElementById('lease-select');
    if (!select.value) return;
    var option    = select.options[select.selectedIndex];
    var invoiced  = option.getAttribute('data-invoiced-months') || '';
    var month     = document.getElementById('period-month').value;
    var year      = document.getElementById('period-year').value;
    var key       = month + '-' + year;
    var warning   = document.getElementById('duplicate-warning');
    var submitBtn = document.getElementById('submit-btn');
    if (invoiced && invoiced.split(',').indexOf(key) !== -1) {
        var mn = document.getElementById('period-month');
        warning.textContent = 'An invoice already exists for ' + mn.options[mn.selectedIndex].text + ' ' + year + '.';
        warning.style.display = 'block';
        submitBtn.disabled = true; submitBtn.style.opacity = '0.5'; submitBtn.style.cursor = 'not-allowed';
    } else {
        warning.style.display = 'none';
        submitBtn.disabled = false; submitBtn.style.opacity = '1'; submitBtn.style.cursor = 'pointer';
    }
}

function loadUtilityCharges() {
    var leaseId = document.getElementById('lease-select').value;
    if (!leaseId) return;
    var month = document.getElementById('period-month').value;
    var year  = document.getElementById('period-year').value;
    document.getElementById('utility-loader').style.display = 'block';
    document.querySelectorAll('.line-item.utility-auto').forEach(function(el) { el.remove(); });
    fetch('/utilities/charges?lease_id=' + leaseId + '&month=' + month + '&year=' + year)
        .then(function(r) { return r.json(); })
        .then(function(charges) {
            document.getElementById('utility-loader').style.display = 'none';
            charges.forEach(function(charge) { addAutoLineItem(charge.description, charge.type, charge.amount); });
            updateTotal();
        })
        .catch(function() { document.getElementById('utility-loader').style.display = 'none'; });
}

function addAutoLineItem(desc, type, amount) {
    var container = document.getElementById('line-items');
    var div = document.createElement('div');
    div.className = 'line-item utility-auto line-item-row';
    div.style.background = '#f9fffe';
    div.innerHTML = buildItemHTML(desc, type, amount);
    container.appendChild(div);
}

function addLineItem() {
    var container = document.getElementById('line-items');
    var div = document.createElement('div');
    div.className = 'line-item line-item-row';
    div.innerHTML = buildItemHTML('', 'other', '');
    container.appendChild(div);
}

function buildItemHTML(desc, type, amount) {
    var types = ['rent','water','electricity','garbage','service_charge','security_levy','penalty','other'];
    var labels = {'service_charge':'Service','security_levy':'Security'};
    var opts = types.map(function(t) {
        var lbl = labels[t] || (t.charAt(0).toUpperCase() + t.slice(1));
        return '<option value="' + t + '"' + (t===type?' selected':'') + '>' + lbl + '</option>';
    }).join('');
    return '<div style="padding:7px 10px"><input type="text" name="descriptions[]" required value="' + (desc||'') + '" placeholder="Description" style="width:100%;border:none;font-size:13px;font-family:\'DM Sans\',sans-serif;outline:none;background:transparent"></div>' +
        '<div style="padding:7px 6px"><select name="types[]" style="width:100%;border:none;font-size:12px;font-family:\'DM Sans\',sans-serif;outline:none;background:transparent">' + opts + '</select></div>' +
        '<div style="padding:7px 6px"><input type="number" name="amounts[]" required min="0" value="' + (amount||'') + '" oninput="updateTotal()" style="width:100%;border:none;font-size:13px;font-family:\'DM Sans\',sans-serif;outline:none;background:transparent"></div>' +
        '<div style="padding:7px;text-align:center"><button type="button" onclick="this.closest(\'.line-item\').remove();updateTotal()" style="background:none;border:none;cursor:pointer;color:#8a8880;font-size:16px;line-height:1">&times;</button></div>';
}

function updateTotal() {
    var amounts = document.querySelectorAll('input[name="amounts[]"]');
    var descs   = document.querySelectorAll('input[name="descriptions[]"]');
    var total = 0, summary = '';
    amounts.forEach(function(input, i) {
        var val = parseFloat(input.value) || 0;
        total += val;
        if (val > 0) {
            var label = descs[i] ? (descs[i].value || 'Item') : 'Item';
            summary += '<div style="display:flex;justify-content:space-between"><span style="color:#8a8880">' + label + '</span><span>{{ currency_symbol() }} ' + val.toLocaleString() + '</span></div>';
        }
    });
    document.getElementById('total-display').textContent = '{{ currency_symbol() }} ' + total.toLocaleString();
    document.getElementById('summary-items').innerHTML = summary || '<div style="color:#8a8880;font-size:12px">No items added yet</div>';
}
</script>
</x-layouts.app>