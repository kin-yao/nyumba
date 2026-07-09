<x-layouts.app>
<style>
.pshow-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }
.pshow-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.pshow-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
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
    min-width: 520px;
}
.unit-cards { display: none; }
.unit-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.unit-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.unit-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 10px;
}
.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 480px;
}
.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 700px) {
    .pshow-kpi { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .unit-cards  { display: block; }
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
    .modal-grid  { grid-template-columns: 1fr; }
}
@media (max-width: 400px) {
    .pshow-kpi { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="pshow-wrap">

    {{-- Header --}}
    <div class="pshow-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('properties.index') }}" style="color:#8a8880;text-decoration:none">Properties</a>
                &rsaquo; {{ $property->name }}
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">
                {{ $property->name }}
            </div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px;line-height:1.6">
                {{ $property->area ?? '' }}{{ $property->area && $property->county ? ', ' : '' }}{{ $property->county ?? '' }}
                &middot; {{ ucfirst($property->type) }}
                @if($property->caretaker_name)
                    <br>Caretaker: {{ $property->caretaker_name }}
                    @if($property->caretaker_phone) ({{ $property->caretaker_phone }}) @endif
                @endif
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0">
            <button onclick="document.getElementById('edit-property-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                Edit property
            </button>
            <button onclick="document.getElementById('delete-property-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:#b91c1c;border:1px solid #fca5a5;border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                Delete property
            </button>
            <a href="{{ route('properties.import.sample', $property) }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none;white-space:nowrap">
                ↓ Sample CSV
            </a>
            <button onclick="document.getElementById('import-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:#1a6b52;border:1px solid #1a6b52;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                ↑ Import CSV
            </button>
            <button onclick="document.getElementById('add-unit-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                + Add unit
            </button>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b;display:flex;align-items:flex-start;gap:10px">
            <span style="flex-shrink:0">⚠</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- KPI strip --}}
    <div class="pshow-kpi">
        @foreach([
            ['Total units',  $totalUnits,          null],
            ['Occupied',     $occupiedCount,        '#15803d'],
            ['Vacant',       $vacantCount,          $vacantCount > 0 ? '#b91c1c' : null],
            ['Occupancy',    $occupancyRate . '%',  $occupancyRate >= 80 ? '#15803d' : '#b91c1c'],
        ] as [$label, $value, $color])
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 20px">
                <div style="font-size:10px;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:7px">{{ $label }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,27px);color:{{ $color ?? '#111110' }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Automatic invoicing ── per property ── --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:24px">
        <div style="font-size:14px;font-weight:500;margin-bottom:14px">Automatic invoice schedule</div>
        <form method="POST" action="{{ route('properties.invoice-schedule', $property) }}" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            @csrf
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;padding:10px 14px;background:#faf9f7;border:1px solid rgba(0,0,0,0.07);border-radius:8px">
                <input type="checkbox" name="auto_invoice_enabled" value="1"
                       {{ $property->auto_invoice_enabled ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:#1a6b52;flex-shrink:0">
                <span style="font-weight:500">Enable automatic invoice generation</span>
            </label>

            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:13px;color:#8a8880">Send invoices on day</span>
                <select name="invoice_send_day" required
                        style="width:100px;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @foreach(range(1,28) as $day)
                        <option value="{{ $day }}" {{ $property->invoice_send_day == $day ? 'selected' : '' }}>
                            {{ $day }}{{ $day==1?'st':($day==2?'nd':($day==3?'rd':'th')) }}
                        </option>
                    @endforeach
                </select>
                <span style="font-size:13px;color:#8a8880">of every month</span>
            </div>

            <button type="submit"
                    style="padding:8px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Save schedule
            </button>
        </form>
    </div>

    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:11px">Units</div>

    @if($units->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
            No units added yet. Add your first unit to get started.
        </div>
    @else
        @php
            $statusColors = [
                'occupied'    => ['bg' => '#dcfce7', 'text' => '#166534'],
                'vacant'      => ['bg' => '#f3f4f6', 'text' => '#4b5563'],
                'maintenance' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
            ];
        @endphp

        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Type</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Rent ({{ currency_symbol() }})</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                        <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                        @php
                            $tenant = $unit->activeLease?->tenant;
                            $colors = $statusColors[$unit->status] ?? $statusColors['vacant'];
                        @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.07)">
                            <td style="padding:11px 14px;font-size:13px"><strong>{{ $unit->name }}</strong></td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $unit->type }}</td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500">{{ number_format($unit->rent_amount) }}</td>
                            <td style="padding:11px 14px;font-size:13px">
                                @if($tenant)
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:26px;height:26px;border-radius:50%;background:#e6f2ed;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                            {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                        </div>
                                        {{ $tenant->full_name }}
                                    </div>
                                @else
                                    <span style="color:#8a8880">Vacant</span>
                                @endif
                            </td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $colors['bg'] }};color:{{ $colors['text'] }}">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>
                            <td style="padding:11px 14px;text-align:right">
                                @if($unit->isVacant())
                                    <a href="{{ route('tenants.create') }}"
                                       style="display:inline-flex;align-items:center;padding:4px 10px;background:#1a6b52;color:#fff;border-radius:6px;font-size:12px;text-decoration:none;font-weight:500">
                                        Move in
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile unit cards --}}
        <div class="unit-cards">
            @foreach($units as $unit)
                @php
                    $tenant = $unit->activeLease?->tenant;
                    $colors = $statusColors[$unit->status] ?? $statusColors['vacant'];
                @endphp
                <div class="unit-card">
                    <div class="unit-card-top">
                        <div>
                            <div style="font-size:15px;font-weight:600;margin-bottom:2px">{{ $unit->name }}</div>
                            <div style="font-size:12px;color:#8a8880">{{ $unit->type }}</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:14px;font-weight:600;color:#111110">{{ currency($unit->rent_amount) }}</div>
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;background:{{ $colors['bg'] }};color:{{ $colors['text'] }};margin-top:3px">
                                {{ ucfirst($unit->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="unit-card-meta">
                        @if($tenant)
                            <div style="display:flex;align-items:center;gap:7px">
                                <div style="width:24px;height:24px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                    {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                </div>
                                <span style="font-size:12px;font-weight:500">{{ $tenant->full_name }}</span>
                            </div>
                        @else
                            <span style="font-size:12px;color:#8a8880">No tenant</span>
                        @endif
                    </div>
                    @if($unit->isVacant())
                        <a href="{{ route('tenants.create') }}"
                           style="display:block;text-align:center;padding:7px;background:#1a6b52;color:#fff;border-radius:7px;font-size:12px;text-decoration:none;font-weight:500">
                            Move in tenant
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Unit Modal --}}
<div id="add-unit-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Add a unit</div>
            <button onclick="document.getElementById('add-unit-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('units.store', $property) }}">
            @csrf
            <div class="modal-grid">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Unit number or name</label>
                    <input name="name" type="text" required placeholder="e.g. A1, Shop 2"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Type</label>
                    <select name="type" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="">Select type</option>
                        <optgroup label="Residential">
                            <option value="Bedsitter">Bedsitter</option>
                            <option value="Studio">Studio</option>
                            <option value="1 bedroom">1 bedroom</option>
                            <option value="2 bedroom">2 bedroom</option>
                            <option value="3 bedroom">3 bedroom</option>
                            <option value="Servant quarter">Servant quarter</option>
                        </optgroup>
                        <optgroup label="Commercial">
                            <option value="Shop">Shop</option>
                            <option value="Office">Office</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Stall">Stall</option>
                            <option value="Godown">Godown</option>
                            <option value="Parking bay">Parking bay</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Monthly rent ({{ currency_symbol() }})</label>
                    <input name="rent_amount" type="number" required min="0" step="0.01" placeholder="e.g. 9500"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Deposit ({{ currency_symbol() }})</label>
                    <input name="deposit_amount" type="number" required min="0" step="0.01" placeholder="e.g. 19000"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:20px;flex-wrap:wrap">
                <button type="submit"
                        style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save unit
                </button>
                <button type="button" onclick="document.getElementById('add-unit-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import CSV Modal --}}
<div id="import-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:480px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div>
                <div style="font-size:15px;font-weight:500">Import units &amp; tenants</div>
                <div style="font-size:12px;color:#8a8880;margin-top:2px">{{ $property->name }}</div>
            </div>
            <button onclick="document.getElementById('import-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <div style="background:#f5f4f0;border-radius:8px;padding:14px;margin-bottom:18px;font-size:13px;color:#8a8880;line-height:1.6">
            Upload a CSV file with your unit and tenant data. Existing units will be skipped.
            <br><br>
            <a href="{{ route('properties.import.sample', $property) }}"
               style="color:#1a6b52;font-weight:500;text-decoration:none">
                ↓ Download sample CSV
            </a>
            to see the required format.
        </div>
        <form method="POST" action="{{ route('properties.import.preview', $property) }}" enctype="multipart/form-data">
            @csrf
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
                    Preview import
                </button>
                <button type="button"
                        onclick="document.getElementById('import-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Property Modal --}}
<div id="edit-property-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:flex-start;justify-content:center;padding:16px;overflow-y:auto">
    <div class="modal-inner" style="max-width:520px;max-height:calc(100vh - 32px);overflow-y:auto;margin:16px 0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Edit property</div>
            <button onclick="document.getElementById('edit-property-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('properties.update', $property) }}">
            @csrf
            @method('PUT')
            <div class="modal-grid" style="margin-bottom:14px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Property name</label>
                    <input name="name" type="text" required value="{{ $property->name }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Type</label>
                    <select name="type" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="residential" {{ $property->type=='residential'?'selected':'' }}>Residential</option>
                        <option value="commercial" {{ $property->type=='commercial'?'selected':'' }}>Commercial</option>
                        <option value="mixed" {{ $property->type=='mixed'?'selected':'' }}>Mixed</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">County</label>
                    <input name="county" type="text" value="{{ $property->county }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Area</label>
                    <input name="area" type="text" value="{{ $property->area }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div style="grid-column:1/-1">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Address</label>
                    <input name="address" type="text" value="{{ $property->address }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Caretaker name</label>
                    <input name="caretaker_name" type="text" value="{{ $property->caretaker_name }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Caretaker phone</label>
                    <input name="caretaker_phone" type="text" value="{{ $property->caretaker_phone }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
            </div>

            <div style="border-top:1px solid rgba(0,0,0,0.07);padding-top:14px;margin-bottom:14px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px">Payment collection</label>
                <div style="display:flex;gap:16px;margin-bottom:10px">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                        <input type="radio" name="payment_type" value="paybill" {{ $property->payment_type=='paybill'?'checked':'' }} onchange="document.getElementById('edit-paybill-fields').style.display='block';document.getElementById('edit-till-fields').style.display='none'">
                        Paybill
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                        <input type="radio" name="payment_type" value="till" {{ $property->payment_type=='till'?'checked':'' }} onchange="document.getElementById('edit-paybill-fields').style.display='none';document.getElementById('edit-till-fields').style.display='block'">
                        Till number
                    </label>
                </div>
                <div id="edit-paybill-fields" style="display:{{ $property->payment_type=='paybill'?'block':'none' }}">
                    <div class="modal-grid">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Business number</label>
                            <input name="business_number" type="text" value="{{ $property->business_number }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Account format</label>
                            <select name="account_format" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                <option value="unit_number" {{ $property->account_format=='unit_number'?'selected':'' }}>Unit number</option>
                                <option value="tenant_name" {{ $property->account_format=='tenant_name'?'selected':'' }}>Tenant name</option>
                                <option value="phone_number" {{ $property->account_format=='phone_number'?'selected':'' }}>Phone number</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="edit-till-fields" style="display:{{ $property->payment_type=='till'?'block':'none' }}">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Till number</label>
                    <input name="till_number" type="text" value="{{ $property->till_number }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                <textarea name="notes" rows="2" style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical">{{ $property->notes }}</textarea>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        style="padding:7px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save changes
                </button>
                <button type="button" onclick="document.getElementById('edit-property-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Property Modal — strong warning, type-to-confirm --}}
<div id="delete-property-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner" style="max-width:460px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:600;color:#991b1b">Delete "{{ $property->name }}"</div>
            <button onclick="closeDeleteModal()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>

        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px;margin-bottom:18px;font-size:13px;color:#7f1d1d;line-height:1.7">
            <strong>This cannot be undone.</strong> Deleting this property will permanently delete:
            <ul style="margin:8px 0 0 18px;padding:0">
                <li>{{ $totalUnits }} {{ Str::plural('unit', $totalUnits) }}</li>
                <li>{{ $invoiceCount }} {{ Str::plural('invoice', $invoiceCount) }}</li>
                <li>{{ $paymentCount }} payment {{ Str::plural('record', $paymentCount) }}</li>
            </ul>
            @if($activeTenantCount > 0)
                <div style="margin-top:10px;font-weight:600">
                    ⚠ {{ $activeTenantCount }} active {{ Str::plural('tenancy', $activeTenantCount) }} currently living here will lose their entire rent, invoice and payment history.
                </div>
            @endif
            @if($expenseCount > 0)
                <div style="margin-top:8px">{{ $expenseCount }} recorded {{ Str::plural('expense', $expenseCount) }} will be kept but unlinked from this property.</div>
            @endif
        </div>

        <form method="POST" action="{{ route('properties.destroy', $property) }}" id="delete-property-form">
            @csrf
            @method('DELETE')
            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:500;color:#991b1b;margin-bottom:8px">
                    Type <strong>{{ $property->name }}</strong> to confirm:
                </label>
                <input type="text" name="confirmation" id="delete-property-confirmation" autocomplete="off"
                       oninput="checkDeletePropertyInput(this)"
                       style="width:100%;height:40px;padding:0 14px;border:2px solid #fca5a5;border-radius:7px;font-size:15px;font-family:'DM Sans',sans-serif;outline:none">
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" id="delete-property-submit-btn" disabled
                        style="padding:8px 20px;background:#e5e7eb;color:#9ca3af;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:not-allowed;font-family:'DM Sans',sans-serif;transition:all .15s"
                        onclick="return confirm('Last chance — this will permanently delete this property and its history. Are you absolutely sure?')">
                    Yes, delete this property
                </button>
                <button type="button" onclick="closeDeleteModal()"
                        style="padding:8px 15px;background:transparent;color:#6b7280;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closeDeleteModal() {
    document.getElementById('delete-property-modal').style.display = 'none';
    document.getElementById('delete-property-confirmation').value = '';
    checkDeletePropertyInput(document.getElementById('delete-property-confirmation'));
}

function checkDeletePropertyInput(input) {
    var btn   = document.getElementById('delete-property-submit-btn');
    var valid = input.value.trim() === @json($property->name);
    btn.disabled         = !valid;
    btn.style.background = valid ? '#b91c1c' : '#e5e7eb';
    btn.style.color      = valid ? '#fff'    : '#9ca3af';
    btn.style.cursor     = valid ? 'pointer' : 'not-allowed';
}
</script>

</x-layouts.app>