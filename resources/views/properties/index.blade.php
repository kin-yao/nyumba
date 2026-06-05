<x-layouts.app>
<style>
.props-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.props-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.props-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.prop-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 10px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.pt-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 12px;
    border-radius: 10px;
    cursor: pointer;
    text-align: center;
    border: 2px solid rgba(0,0,0,0.1);
    background: #fff;
}
.pt-label.active {
    border-color: #1a6b52;
    background: #f0fdf4;
}

@media (max-width: 900px) {
    .props-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .props-grid  { grid-template-columns: 1fr; }
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
    .modal-grid  { grid-template-columns: 1fr; }
}
</style>

<div class="props-wrap">

    <div class="props-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Properties</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ $properties->count() }} {{ Str::plural('property', $properties->count()) }}
            </div>
        </div>
        <button onclick="openPropertyModal()"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap;flex-shrink:0">
            + Add property
        </button>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    @if($properties->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:#8a8880;font-size:13px">
            <div style="font-size:40px;margin-bottom:12px">🏠</div>
            <div style="font-weight:500;margin-bottom:4px">No properties yet</div>
            <div>Add your first property to get started</div>
        </div>
    @else
        <div class="props-grid">
            @foreach($properties as $property)
                @php
                    $occupancyPct = $property->units_count > 0
                        ? round(($property->occupied_units_count / $property->units_count) * 100)
                        : 0;
                @endphp
                <a href="{{ route('properties.show', $property) }}"
                   style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px;text-decoration:none;color:inherit;display:block">

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;gap:8px">
                        <div style="min-width:0">
                            <div style="font-size:13px;font-weight:500;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $property->name }}</div>
                            <div style="font-size:11px;color:#8a8880;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $property->area ?? $property->county }}
                                &middot; {{ ucfirst($property->type) }}
                                @if($property->caretaker_name)
                                    &middot; {{ $property->caretaker_name }}
                                @endif
                            </div>
                        </div>
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;flex-shrink:0;
                            background:{{ $occupancyPct >= 85 ? '#dcfce7' : ($occupancyPct >= 60 ? '#fef3c7' : '#fee2e2') }};
                            color:{{ $occupancyPct >= 85 ? '#166534' : ($occupancyPct >= 60 ? '#92400e' : '#991b1b') }}">
                            {{ $occupancyPct }}%
                        </span>
                    </div>

                    <div class="prop-stats">
                        <div>
                            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Units</div>
                            <div style="font-family:'DM Serif Display',serif;font-size:20px">{{ $property->units_count }}</div>
                        </div>
                        <div>
                            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Occupied</div>
                            <div style="font-family:'DM Serif Display',serif;font-size:20px;color:#15803d">{{ $property->occupied_units_count }}</div>
                        </div>
                        <div>
                            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Vacant</div>
                            <div style="font-family:'DM Serif Display',serif;font-size:20px;color:{{ ($property->units_count - $property->occupied_units_count) > 0 ? '#b91c1c' : '#8a8880' }}">
                                {{ $property->units_count - $property->occupied_units_count }}
                            </div>
                        </div>
                    </div>

                    <div style="height:4px;background:#ece9e2;border-radius:2px;overflow:hidden;margin-bottom:10px">
                        <div style="height:100%;background:{{ $occupancyPct >= 60 ? '#1a6b52' : '#b91c1c' }};border-radius:2px;width:{{ $occupancyPct }}%"></div>
                    </div>

                    <div style="padding-top:8px;border-top:1px solid rgba(0,0,0,0.05)">
                        @if($property->payment_type === 'paybill')
                            <span style="font-size:11px;color:#166534">
                                📱 Paybill &middot; {{ $property->business_number }}
                            </span>
                        @elseif($property->payment_type === 'till')
                            <span style="font-size:11px;color:#92400e">
                                🔢 Till &middot; {{ $property->till_number }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#d97706">⚠ Payment not configured</span>
                        @endif
                    </div>
                </a>
            @endforeach

            <div onclick="openPropertyModal()"
                 style="background:#fff;border-radius:10px;border:1px dashed rgba(0,0,0,0.15);padding:18px 20px;cursor:pointer;display:flex;align-items:center;justify-content:center;min-height:160px">
                <div style="text-align:center;color:#8a8880">
                    <div style="font-size:24px;margin-bottom:5px">+</div>
                    <div style="font-size:13px">Add property</div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Add Property Modal --}}
<div id="add-property-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">

        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div>
                <div style="font-size:15px;font-weight:500">Add a property</div>
                <div id="modal-step-label" style="font-size:11px;color:#8a8880;margin-top:2px">Step 1 of 2 — Property details</div>
            </div>
            <button onclick="closePropertyModal()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1;flex-shrink:0;margin-left:12px">&times;</button>
        </div>

        <div style="display:flex;gap:6px;margin-bottom:22px">
            <div id="step-dot-1" style="height:3px;flex:1;border-radius:2px;background:#1a6b52"></div>
            <div id="step-dot-2" style="height:3px;flex:1;border-radius:2px;background:#ece9e2"></div>
        </div>

        <form method="POST" action="{{ route('properties.store') }}">
            @csrf

            {{-- Step 1 --}}
            <div id="step-1">
                <div class="modal-grid">
                    <div style="grid-column:span 2">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Property name</label>
                        <input name="name" type="text" required placeholder="e.g. Westlands Heights" value="{{ old('name') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Type</label>
                        <select name="type" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select type</option>
                            <option value="residential" {{ old('type')=='residential'?'selected':'' }}>Residential</option>
                            <option value="commercial"  {{ old('type')=='commercial' ?'selected':'' }}>Commercial</option>
                            <option value="mixed"       {{ old('type')=='mixed'      ?'selected':'' }}>Mixed use</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">County</label>
                        <select name="county" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="">Select county</option>
                            @foreach(['Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit','Meru','Migori','Mombasa',"Murang'a",'Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu','Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia','Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot'] as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Area / Estate</label>
                        <input name="area" type="text" placeholder="e.g. Westlands, Kilimani" value="{{ old('area') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Address</label>
                        <input name="address" type="text" placeholder="Street address (optional)" value="{{ old('address') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Caretaker name</label>
                        <input name="caretaker_name" type="text" placeholder="Optional" value="{{ old('caretaker_name') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Caretaker phone</label>
                        <input name="caretaker_phone" type="text" placeholder="07XX or 01XX" value="{{ old('caretaker_phone') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div style="grid-column:span 2">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Any additional notes..."
                                  style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:20px;flex-wrap:wrap">
                    <button type="button" onclick="goToStep(2)"
                            style="padding:7px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Next: Payment setup →
                    </button>
                    <button type="button" onclick="closePropertyModal()"
                            style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Step 2 --}}
            <div id="step-2" style="display:none">

                <p style="font-size:13px;color:#8a8880;margin-bottom:18px;line-height:1.6">
                    How do tenants pay rent for this property?
                </p>

                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px">Payment type</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">

                        <label class="pt-label active" id="pt-label-paybill" onclick="switchPayType('paybill')">
                            <input type="radio" name="payment_type" value="paybill" checked style="display:none">
                            <span style="font-size:24px">📱</span>
                            <span style="font-size:12px;font-weight:500">Paybill</span>
                            <span style="font-size:10px;color:#8a8880;line-height:1.4">Supports auto payment matching</span>
                        </label>

                        <label class="pt-label" id="pt-label-till" onclick="switchPayType('till')">
                            <input type="radio" name="payment_type" value="till" style="display:none">
                            <span style="font-size:24px">🔢</span>
                            <span style="font-size:12px;font-weight:500">Till number</span>
                            <span style="font-size:10px;color:#8a8880;line-height:1.4">Manual payment recording only</span>
                        </label>

                    </div>
                </div>

                {{-- Paybill fields --}}
                <div id="pay-fields-paybill" style="display:block;margin-bottom:16px">
                    <div style="display:grid;gap:13px">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Business number</label>
                            <input name="business_number" type="text" placeholder="e.g. 522522"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Account reference format</label>
                            <select name="account_format" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                <option value="unit_number">Unit number (e.g. A1, B3)</option>
                                <option value="tenant_name">Tenant name</option>
                                <option value="phone_number">Tenant phone number</option>
                            </select>
                            <div style="font-size:11px;color:#8a8880;margin-top:3px">What tenants type as the account reference when paying</div>
                        </div>
                        <div style="background:#e6f2ed;border:1px solid #a7d7c5;border-radius:8px;padding:10px 13px;font-size:12px;color:#166534;line-height:1.5">
                            ✓ Paybill supports automatic payment matching. Contact us after saving to complete setup on our end.
                        </div>
                    </div>
                </div>

                {{-- Till fields --}}
                <div id="pay-fields-till" style="display:none;margin-bottom:16px">
                    <div style="display:grid;gap:13px">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Till number</label>
                            <input name="till_number" type="text" placeholder="e.g. 123456"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:10px 13px;font-size:12px;color:#92400e;line-height:1.5">
                            ⚠ Till numbers do not support automatic payment matching. You will need to record payments manually.
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:8px;margin-top:20px;flex-wrap:wrap">
                    <button type="submit"
                            style="padding:7px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Save property
                    </button>
                    <button type="button" onclick="goToStep(1)"
                            style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        ← Back
                    </button>
                    <button type="button" onclick="closePropertyModal()"
                            style="padding:7px 15px;background:transparent;color:#8a8880;border:none;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Cancel
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
function openPropertyModal() {
    document.getElementById('add-property-modal').style.display = 'flex';
    goToStep(1);
    switchPayType('paybill');
}

function closePropertyModal() {
    document.getElementById('add-property-modal').style.display = 'none';
    goToStep(1);
    switchPayType('paybill');
}

function goToStep(n) {
    document.getElementById('step-1').style.display = n === 1 ? 'block' : 'none';
    document.getElementById('step-2').style.display = n === 2 ? 'block' : 'none';
    document.getElementById('modal-step-label').textContent =
        n === 1 ? 'Step 1 of 2 — Property details' : 'Step 2 of 2 — Payment setup';
    document.getElementById('step-dot-1').style.background = '#1a6b52';
    document.getElementById('step-dot-2').style.background = n === 2 ? '#1a6b52' : '#ece9e2';
}

function switchPayType(type) {
    document.querySelectorAll('input[name="payment_type"]').forEach(function(r) {
        r.checked = r.value === type;
    });
    ['paybill', 'till'].forEach(function(k) {
        var el = document.getElementById('pt-label-' + k);
        if (!el) return;
        k === type ? el.classList.add('active') : el.classList.remove('active');
    });
    document.getElementById('pay-fields-paybill').style.display = type === 'paybill' ? 'block' : 'none';
    document.getElementById('pay-fields-till').style.display    = type === 'till'    ? 'block' : 'none';
}
</script>
</x-layouts.app>