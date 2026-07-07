<x-layouts.app>
<style>
.util-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.util-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.util-controls {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.util-period-form {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.reading-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 8px;
    gap: 10px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 400px;
}

@media (max-width: 600px) {
    .util-controls { width: 100%; }
    .util-period-form { width: 100%; }
    .util-period-form input[type="number"] { width: 70px; }
    .reading-row { flex-direction: column; align-items: flex-start; gap: 10px; }
    .reading-row button { width: 100%; }
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
}
</style>

<div class="util-wrap">

    <div class="util-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Utilities</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                Readings for {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </div>
        </div>
        <div class="util-controls">
            <div style="position:relative">
                <svg style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#8a8880;pointer-events:none" width="13" height="13" viewBox="0 0 13 13" fill="none">
                    <circle cx="5.5" cy="5.5" r="4" stroke="currentColor" stroke-width="1.2"/>
                    <path d="M9 9l2.5 2.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
                <input type="text" id="unit-search" placeholder="Search unit e.g. A1"
                       oninput="filterByUnit(this.value)"
                       style="height:34px;padding:0 11px 0 30px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:160px">
            </div>
            <form method="GET" action="{{ route('utilities.index') }}" class="util-period-form">
                <select name="month" onchange="this.form.submit()"
                        style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                            {{ \Carbon\Carbon::createFromDate($year,$m,1)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <input name="year" type="number" value="{{ $year }}"
                       style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:80px">
                <button type="submit" style="height:34px;padding:0 12px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Go
                </button>
            </form>
            <a href="{{ route('utilities.readings.csv.download', ['month' => $month, 'year' => $year]) }}"
               style="display:inline-flex;align-items:center;padding:7px 14px;background:transparent;color:#1a6b52;border:1px solid #1a6b52;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap">
                ↓ Download CSV
            </a>
            <button onclick="document.getElementById('upload-csv-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;padding:7px 14px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                ↑ Upload CSV
            </button>
            <a href="{{ route('utilities.rates') }}"
               style="display:inline-flex;align-items:center;padding:7px 14px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none;white-space:nowrap">
                Configure rates
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
    @if(session('utility_csv_skipped'))
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:12px;color:#92400e;line-height:1.6">
            <div style="font-weight:600;margin-bottom:4px">Rows skipped:</div>
            @foreach(session('utility_csv_skipped') as $line)
                <div>&middot; {{ $line }}</div>
            @endforeach
        </div>
    @endif

    <div id="no-results" style="display:none;background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:40px;text-align:center;color:#8a8880;font-size:13px">
        No unit found matching your search.
    </div>

    @php $hasAny = false; @endphp

    @foreach($properties as $property)
        @php
            $occupiedUnits = $property->units->filter(fn($u) => $u->activeLease !== null);
            $meterRates    = $property->utilityRates->whereIn('billing_type', ['per_unit','per_meter_reading']);
            $flatRates     = $property->utilityRates->where('billing_type','flat_fee');
        @endphp

        @if($occupiedUnits->isNotEmpty())
            @php $hasAny = true; @endphp
            <div class="property-section" style="margin-bottom:28px">
                <div style="font-size:13px;font-weight:500;margin-bottom:10px">{{ $property->name }}</div>

                @if($meterRates->isEmpty() && $flatRates->isEmpty())
                    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:11px 15px;font-size:13px;color:#92400e;margin-bottom:10px">
                        No utility rates configured.
                        <a href="{{ route('utilities.rates') }}" style="color:#92400e;font-weight:500">Configure rates</a> first.
                    </div>
                @endif

                @foreach($occupiedUnits as $unit)
                    @php
                        $tenant = $unit->activeLease->tenant;
                        $totalCharges = 0;
                        foreach($meterRates as $rate) {
                            $key = $unit->id.'_'.$rate->type;
                            $reading = $readings->get($key)?->first();
                            if($reading) $totalCharges += $reading->charge_amount;
                        }
                        foreach($flatRates as $rate) { $totalCharges += $rate->amount; }
                    @endphp

                    <div class="unit-card" data-unit="{{ strtolower($unit->name) }}"
                         style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);margin-bottom:8px">

                        <div style="padding:12px 16px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;align-items:center;justify-content:space-between;background:#faf9f7;border-radius:10px 10px 0 0;flex-wrap:wrap;gap:8px">
                            <div style="display:flex;align-items:center;gap:9px">
                                <div style="width:28px;height:28px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                    {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                </div>
                                <div>
                                    <span style="font-size:13px;font-weight:500">{{ $tenant->full_name }}</span>
                                    <span style="font-size:12px;color:#8a8880;margin-left:6px">Unit <strong>{{ $unit->name }}</strong></span>
                                </div>
                            </div>
                            @if($totalCharges > 0)
                                <span style="font-size:12px;font-weight:500;color:#1a6b52">Total: {{ currency($totalCharges) }}</span>
                            @endif
                        </div>

                        <div style="padding:14px 16px">
                            @if($meterRates->isEmpty() && $flatRates->isEmpty())
                                <div style="font-size:13px;color:#8a8880">No rates configured.</div>
                            @else
                                <div style="display:grid;gap:8px">
                                    @foreach($meterRates as $rate)
                                        @php
                                            $key         = $unit->id.'_'.$rate->type;
                                            $reading     = $readings->get($key)?->first();
                                            $lastReading = $lastReadings->get($key)?->first();
                                            $prevValue   = $lastReading ? $lastReading->current_reading : 0;
                                            $hasPrev     = $lastReading !== null;
                                        @endphp
                                        <div class="reading-row">
                                            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                                                <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:{{ $reading?'#1a6b52':'#d1d5db' }}"></div>
                                                <div style="min-width:0">
                                                    <div style="font-size:13px;font-weight:500">{{ $rate->name }}</div>
                                                    <div style="font-size:11px;color:#8a8880;flex-wrap:wrap">
                                                        {{ currency($rate->amount, 2) }} per unit (configured)
                                                        @if($reading)
                                                            &middot; {{ number_format($reading->units_consumed,1) }} units
                                                            &middot; <span style="color:#1a6b52;font-weight:500">{{ currency($reading->charge_amount) }}</span>
                                                        @else
                                                            &middot; <span style="color:#d97706">No reading</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <button onclick="openModal({{ $unit->id }},'{{ $unit->name }}','{{ addslashes($tenant->full_name) }}','{{ $rate->type }}','{{ addslashes($rate->name) }}',{{ $rate->amount }},{{ $prevValue }},{{ $hasPrev ? 'true' : 'false' }},{{ $reading ? $reading->current_reading : 'null' }})"
                                                    style="flex-shrink:0;font-size:12px;padding:5px 12px;background:{{ $reading?'transparent':'#1a6b52' }};color:{{ $reading?'#8a8880':'#fff' }};border:1px solid {{ $reading?'rgba(0,0,0,0.1)':'#1a6b52' }};border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                                                {{ $reading ? 'Update' : 'Enter reading' }}
                                            </button>
                                        </div>
                                    @endforeach

                                    @foreach($flatRates as $rate)
                                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border:1px solid rgba(0,0,0,0.07);border-radius:8px;background:#f9fffe;flex-wrap:wrap;gap:8px">
                                            <div style="display:flex;align-items:center;gap:10px">
                                                <div style="width:8px;height:8px;border-radius:50%;background:#1a6b52;flex-shrink:0"></div>
                                                <div>
                                                    <div style="font-size:13px;font-weight:500">{{ $rate->name }}</div>
                                                    <div style="font-size:11px;color:#8a8880">Flat fee &middot; auto-billed</div>
                                                </div>
                                            </div>
                                            <span style="font-size:13px;font-weight:500;color:#1a6b52">{{ currency($rate->amount) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    @if(!$hasAny)
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">💧</div>
            <div style="font-weight:500;margin-bottom:4px">No occupied units found</div>
            <div>Move in tenants first, then configure utility rates</div>
        </div>
    @endif
</div>

{{-- Reading Modal --}}
<div id="reading-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div>
                <div style="font-size:15px;font-weight:500" id="modal-title">Enter reading</div>
                <div style="font-size:12px;color:#8a8880;margin-top:2px" id="modal-subtitle"></div>
            </div>
            <button onclick="document.getElementById('reading-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;margin-left:12px;line-height:1">&times;</button>
        </div>

        <form method="POST" action="{{ route('utilities.store') }}">
            @csrf
            <input type="hidden" name="unit_id"         id="modal-unit-id">
            <input type="hidden" name="utility_type"    id="modal-utility-type">
            <input type="hidden" name="previous_reading" id="modal-prev-hidden">
            <input type="hidden" name="reading_month"   value="{{ $month }}">
            <input type="hidden" name="reading_year"    value="{{ $year }}">

            {{-- Previous reading --}}
            <div style="margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <label style="font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase">Previous reading</label>
                    <span id="modal-prev-source" style="font-size:10px;color:#8a8880;font-style:italic"></span>
                </div>
                {{-- Locked: shown when last month's reading exists --}}
                <div id="modal-prev-locked"
                     style="display:none;width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.08);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#4b5563;align-items:center">
                    <span id="modal-prev-display">0</span>
                </div>
                {{-- Editable: shown only for first-ever reading --}}
                <input id="modal-prev-editable" type="number" step="0.01" min="0"
                       oninput="syncPrev(this.value)"
                       placeholder="Enter opening meter reading"
                       style="display:none;width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
            </div>

            {{-- Current reading --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Current reading</label>
                <input name="current_reading" type="number" step="0.01" required id="modal-curr"
                       oninput="calcCharge()"
                       style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
            </div>

            {{-- Summary --}}
            <div style="background:#f5f4f0;border-radius:8px;padding:14px 16px;margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;margin-bottom:7px">
                    <span style="font-size:12px;color:#8a8880">Units consumed</span>
                    <span style="font-size:13px;font-weight:500" id="modal-units">0</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:7px">
                    <span style="font-size:12px;color:#8a8880">Rate per unit (configured)</span>
                    <span style="font-size:13px;font-weight:500" id="modal-rate-display">{{ currency_symbol() }} 0</span>
                </div>
                <div style="border-top:1px solid rgba(0,0,0,0.08);padding-top:8px;display:flex;justify-content:space-between">
                    <span style="font-size:12px;color:#8a8880">Estimated charge</span>
                    <span style="font-family:'DM Serif Display',serif;font-size:20px;color:#1a6b52" id="modal-charge">{{ currency_symbol() }} 0</span>
                </div>
            </div>

            <div style="font-size:11px;color:#8a8880;margin-bottom:14px">
                Actual charge uses the configured property rate.
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        style="flex:1;padding:8px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save reading
                </button>
                <button type="button" onclick="document.getElementById('reading-modal').style.display='none'"
                        style="padding:8px 14px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var currentRate = 0;
var lockedPrev  = 0;

function openModal(unitId, unitName, tenantName, type, rateName, rate, prevValue, hasPrev, currentReading) {
    currentRate = parseFloat(rate) || 0;
    lockedPrev  = parseFloat(prevValue) || 0;

    document.getElementById('reading-modal').style.display  = 'flex';
    document.getElementById('modal-unit-id').value          = unitId;
    document.getElementById('modal-utility-type').value     = type;
    document.getElementById('modal-title').textContent      = rateName + ' — Unit ' + unitName;
    document.getElementById('modal-subtitle').textContent   = tenantName;
    document.getElementById('modal-rate-display').textContent = '{{ currency_symbol() }} ' + currentRate.toLocaleString();

    var locked   = document.getElementById('modal-prev-locked');
    var editable = document.getElementById('modal-prev-editable');
    var source   = document.getElementById('modal-prev-source');
    var hidden   = document.getElementById('modal-prev-hidden');
    var curr     = document.getElementById('modal-curr');

    if (hasPrev) {
        // Last month's reading exists — lock previous reading
        locked.style.display   = 'flex';
        editable.style.display = 'none';
        document.getElementById('modal-prev-display').textContent = lockedPrev.toLocaleString();
        source.textContent = 'Carried from last month';
        hidden.value       = lockedPrev;
    } else {
        // No prior reading — first entry, allow manual baseline
        locked.style.display   = 'none';
        editable.style.display = 'block';
        editable.value         = '';
        source.textContent     = 'First reading — enter opening value';
        hidden.value           = 0;
    }

    // If updating an existing reading, pre-fill current reading
    curr.value = (currentReading !== null && currentReading !== undefined) ? currentReading : '';

    calcCharge();
    setTimeout(() => curr.focus(), 100);
}

function syncPrev(val) {
    lockedPrev = parseFloat(val) || 0;
    document.getElementById('modal-prev-hidden').value = lockedPrev;
    calcCharge();
}

function calcCharge() {
    var prev   = parseFloat(document.getElementById('modal-prev-hidden').value) || 0;
    var curr   = parseFloat(document.getElementById('modal-curr').value) || 0;
    var units  = Math.max(0, curr - prev);
    var charge = units * currentRate;
    document.getElementById('modal-units').textContent  = units.toFixed(1);
    document.getElementById('modal-charge').textContent = '{{ currency_symbol() }} ' + charge.toLocaleString();
}

function filterByUnit(query) {
    query = query.toLowerCase().trim();
    var cards      = document.querySelectorAll('.unit-card');
    var anyVisible = false;
    cards.forEach(card => {
        var show = query === '' || (card.getAttribute('data-unit') || '').includes(query);
        card.style.display = show ? 'block' : 'none';
        if (show) anyVisible = true;
    });
    document.getElementById('no-results').style.display = (!anyVisible && query !== '') ? 'block' : 'none';
}
</script>

{{-- Upload readings CSV modal --}}
<div id="upload-csv-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:460px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Upload readings CSV</div>
            <button onclick="document.getElementById('upload-csv-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <div style="background:#f5f4f0;border-radius:8px;padding:14px;margin-bottom:18px;font-size:13px;color:#8a8880;line-height:1.6">
            Fill in the <strong>current_reading</strong> column on the CSV you downloaded and upload it here.
            Rows left blank are skipped. The month/year below must match the period on the CSV you downloaded.
        </div>
        <form method="POST" action="{{ route('utilities.readings.csv.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:flex;gap:10px;margin-bottom:16px">
                <div style="flex:1">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Month</label>
                    <select name="month" required
                            style="width:100%;height:36px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                                {{ \Carbon\Carbon::createFromDate($year,$m,1)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Year</label>
                    <input name="year" type="number" required value="{{ $year }}"
                           style="width:100%;height:36px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
            </div>
            <div style="margin-bottom:18px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px">
                    Select CSV file
                </label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                       style="width:100%;font-size:13px;font-family:'DM Sans',sans-serif;color:#111110">
                <div style="font-size:11px;color:#8a8880;margin-top:5px">Max 5MB. Must be a .csv file.</div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        style="padding:7px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Upload &amp; update readings
                </button>
                <button type="button"
                        onclick="document.getElementById('upload-csv-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>