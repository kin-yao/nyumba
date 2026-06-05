<x-layouts.app>
<style>
.rates-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.rates-header {
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
    min-width: 480px;
}

/* Mobile rate cards */
.rate-cards { display: none; }
.rate-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.rate-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.rate-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 13px;
}

@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .rate-cards  { display: block; }
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
    .modal-2col  { grid-template-columns: 1fr; }
}
</style>

<div class="rates-wrap">

    <div class="rates-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('utilities.index') }}" style="color:#8a8880;text-decoration:none">Utilities</a>
                &rsaquo; Rates
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Utility rates</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">Configure rates used in billing</div>
        </div>
        <button onclick="document.getElementById('add-rate-modal').style.display='flex'"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap;flex-shrink:0">
            + Add rate
        </button>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    @php $allEmpty = $properties->every(fn($p) => $p->utilityRates->isEmpty()); @endphp

    @if($allEmpty)
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">⚡</div>
            <div style="font-weight:500;margin-bottom:4px">No rates configured yet</div>
            <div>Add rates for water, garbage, and other recurring charges</div>
        </div>
    @else
        @foreach($properties as $property)
            @if($property->utilityRates->isNotEmpty())
                <div style="margin-bottom:20px">
                    <div style="font-size:13px;font-weight:500;margin-bottom:10px">{{ $property->name }}</div>

                    {{-- Desktop table --}}
                    <div class="tbl-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Name</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Type</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Amount</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Billing type</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Auto-bill</th>
                                    <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($property->utilityRates as $rate)
                                    <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                                        <td style="padding:11px 14px;font-size:13px;font-weight:500">{{ $rate->name }}</td>
                                        <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ ucfirst($rate->type) }}</td>
                                        <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right">{{ currency($rate->amount, 2) }}</td>
                                        <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ ucfirst(str_replace('_',' ',$rate->billing_type)) }}</td>
                                        <td style="padding:11px 14px">
                                            @if($rate->auto_bill)
                                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#dcfce7;color:#166534">Yes</span>
                                            @else
                                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#f3f4f6;color:#4b5563">No</span>
                                            @endif
                                        </td>
                                        <td style="padding:11px 14px;text-align:right">
                                            <form method="POST" action="{{ route('utilities.rates.destroy', $rate) }}"
                                                  onsubmit="return confirm('Remove this rate?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        style="display:inline-flex;padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile rate cards --}}
                    <div class="rate-cards">
                        @foreach($property->utilityRates as $rate)
                            <div class="rate-card">
                                <div class="rate-card-top">
                                    <div>
                                        <div style="font-weight:500;font-size:13px;margin-bottom:2px">{{ $rate->name }}</div>
                                        <div style="font-size:11px;color:#8a8880">{{ ucfirst($rate->type) }}</div>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0">
                                        <div style="font-size:15px;font-weight:600">{{ currency($rate->amount, 2) }}</div>
                                        <div style="font-size:11px;color:#8a8880">per {{ $rate->billing_type === 'flat_fee' ? 'tenant' : 'unit' }}</div>
                                    </div>
                                </div>
                                <div class="rate-card-meta">
                                    <span style="font-size:11px;color:#8a8880;background:#f5f4f0;padding:2px 8px;border-radius:20px">
                                        {{ ucfirst(str_replace('_',' ',$rate->billing_type)) }}
                                    </span>
                                    @if($rate->auto_bill)
                                        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;background:#dcfce7;color:#166534">Auto-bill</span>
                                    @else
                                        <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#f3f4f6;color:#4b5563">Manual</span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('utilities.rates.destroy', $rate) }}"
                                      onsubmit="return confirm('Remove this rate?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            style="width:100%;padding:7px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

{{-- Add Rate Modal --}}
<div id="add-rate-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Add utility rate</div>
            <button onclick="document.getElementById('add-rate-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('utilities.rates.store') }}">
            @csrf
            <div style="margin-bottom:13px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Property</label>
                <select name="property_id" required
                        style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <option value="" disabled selected>Select property</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-2col">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Rate name</label>
                    <input name="name" type="text" required placeholder="e.g. Water, Garbage"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Type</label>
                    <select name="type" required
                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select type</option>
                        <option value="water">Water</option>
                        <option value="garbage">Garbage</option>
                        <option value="electricity">Electricity</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="modal-2col">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Amount ({{ currency_symbol() }})</label>
                    <input name="amount" type="number" step="0.01" required
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Billing type</label>
                    <select name="billing_type" required
                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select billing type</option>
                        <option value="flat_fee">Flat fee per tenant</option>
                        <option value="per_unit">Per unit consumed</option>
                        <option value="per_meter_reading">Per meter reading</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:18px">
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;cursor:pointer;padding:12px 14px;background:#f5f4f0;border-radius:8px">
                    <input type="checkbox" name="auto_bill" value="1" checked
                           style="accent-color:#1a6b52;width:15px;height:15px;flex-shrink:0">
                    <div>
                        <div style="font-weight:500">Auto-bill</div>
                        <div style="font-size:11px;color:#8a8880;margin-top:1px">Include automatically when generating bulk invoices</div>
                    </div>
                </label>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save rate
                </button>
                <button type="button" onclick="document.getElementById('add-rate-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>