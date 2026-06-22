<x-layouts.app>
<style>
.maint-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.maint-band {
    position: relative;
    background: #0e3f30;
    border-radius: 12px;
    overflow: hidden;
    padding: 20px 24px;
    margin-bottom: 20px;
}
.maint-band-shards { position: absolute; inset: 0; pointer-events: none; }
.maint-band-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.maint-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
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
    min-width: 600px;
}

/* Mobile cards */
.maint-cards { display: none; }
.maint-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.maint-card.urgent { border-left: 3px solid #b91c1c; }
.maint-card-top {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}
.maint-card-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.maint-card-actions {
    display: flex;
    gap: 6px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

@media (max-width: 700px) {
    .maint-kpi { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .tbl-scroll   { display: none; }
    .maint-cards  { display: block; }
    .modal-inner  { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
    .maint-band   { padding: 18px; }
}
</style>

<div class="maint-wrap">

    <div class="maint-band">
        <div class="maint-band-shards">
            <svg width="100%" height="100%" viewBox="0 0 1200 120" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <polygon points="-72,0 792,0 480,120 -72,120" fill="#ffffff" opacity="0.04"/>
                <polygon points="96,0 756,0 360,120 -72,120" fill="#ffffff" opacity="0.05"/>
            </svg>
        </div>
        <div class="maint-band-content">
            <div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1;color:#fff">Maintenance</div>
                <div style="font-size:13px;color:rgba(244,242,236,.6);margin-top:3px">Track and resolve property issues</div>
            </div>
            <button onclick="document.getElementById('log-modal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#fff;color:#0e3f30;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap;flex-shrink:0">
                + Log request
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166634">
            {{ session('success') }}
        </div>
    @endif

    <div class="maint-kpi">
        @foreach([
            ['Open',        $openCount,       null,                              null],
            ['In progress', $inProgressCount, null,                              null],
            ['Resolved',    $resolvedCount,   '#15803d',                         null],
            ['Urgent open', $urgentCount,     $urgentCount > 0 ? '#b91c1c':null, $urgentCount > 0 ? '#fca5a5':null],
        ] as [$label, $value, $color, $border])
            <div style="background:#fff;border-radius:10px;border:1px solid {{ $border ?? 'rgba(0,0,0,0.07)' }};padding:16px 20px">
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">{{ $label }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,27px);color:{{ $color ?? '#111110' }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    @if($requests->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">🔧</div>
            <div style="font-weight:500;margin-bottom:4px">No maintenance requests</div>
            <div>Log a request when something needs attention</div>
        </div>
    @else

        @php
            $priorityColors = [
                'urgent' => ['#fee2e2','#991b1b'],
                'normal' => ['#fef3c7','#92400e'],
                'low'    => ['#f3f4f6','#4b5563'],
            ];
            $statusColors = [
                'open'        => ['#dbeafe','#1e40af'],
                'in_progress' => ['#fef3c7','#92400e'],
                'resolved'    => ['#dcfce7','#166534'],
            ];
        @endphp

        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Issue</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Priority</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Days open</th>
                        <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        @php
                            $pc = $priorityColors[$req->priority] ?? $priorityColors['normal'];
                            $sc = $statusColors[$req->status]    ?? $statusColors['open'];
                            $daysOpen = $req->created_at->diffInDays(now());
                        @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05);{{ $req->priority==='urgent'&&$req->status!=='resolved'?'border-left:3px solid #b91c1c;':'' }}">
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880;white-space:nowrap">{{ $req->created_at->format('d M Y') }}</td>
                            <td style="padding:11px 14px;font-size:13px">
                                <strong>{{ $req->unit->name }}</strong>
                                <div style="font-size:11px;color:#8a8880">{{ $req->unit->property->name }}</div>
                            </td>
                            <td style="padding:11px 14px;font-size:13px;max-width:240px">
                                {{ $req->description }}
                                @if($req->resolution_notes)
                                    <div style="font-size:11px;color:#15803d;margin-top:2px">✓ {{ $req->resolution_notes }}</div>
                                @endif
                            </td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $pc[0] }};color:{{ $pc[1] }}">
                                    {{ ucfirst($req->priority) }}
                                </span>
                            </td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sc[0] }};color:{{ $sc[1] }}">
                                    {{ ucfirst(str_replace('_',' ',$req->status)) }}
                                </span>
                            </td>
                            <td style="padding:11px 14px;font-size:13px;color:{{ $daysOpen>7&&$req->status!=='resolved'?'#b91c1c':'#8a8880' }}">
                                {{ $req->status==='resolved' ? '-' : $daysOpen.'d' }}
                            </td>
                            <td style="padding:11px 14px;text-align:right">
                                <div style="display:flex;gap:6px;justify-content:flex-end">
                                    @if($req->status !== 'resolved')
                                        <button onclick="openUpdateModal({{ $req->id }},'{{ $req->status }}','{{ addslashes($req->unit->name) }}','{{ addslashes($req->description) }}')"
                                                style="display:inline-flex;align-items:center;padding:4px 10px;background:#1a6b52;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                            Update
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('maintenance.destroy', $req) }}"
                                          onsubmit="return confirm('Delete this request?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="maint-cards">
            @foreach($requests as $req)
                @php
                    $pc = $priorityColors[$req->priority] ?? $priorityColors['normal'];
                    $sc = $statusColors[$req->status]    ?? $statusColors['open'];
                    $daysOpen = $req->created_at->diffInDays(now());
                @endphp
                <div class="maint-card {{ $req->priority==='urgent'&&$req->status!=='resolved'?'urgent':'' }}">
                    <div class="maint-card-top">
                        <div style="min-width:0">
                            <div style="font-weight:500;font-size:13px;margin-bottom:2px">
                                Unit {{ $req->unit->name }} &middot; {{ $req->unit->property->name }}
                            </div>
                            <div style="font-size:12px;color:#8a8880">{{ $req->description }}</div>
                            @if($req->resolution_notes)
                                <div style="font-size:11px;color:#15803d;margin-top:3px">✓ {{ $req->resolution_notes }}</div>
                            @endif
                        </div>
                        <div style="font-size:11px;color:#8a8880;flex-shrink:0;text-align:right">
                            {{ $req->created_at->format('d M') }}
                            @if($req->status !== 'resolved')
                                <div style="color:{{ $daysOpen>7?'#b91c1c':'#8a8880' }}">{{ $daysOpen }}d open</div>
                            @endif
                        </div>
                    </div>
                    <div class="maint-card-badges">
                        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;background:{{ $pc[0] }};color:{{ $pc[1] }}">
                            {{ ucfirst($req->priority) }}
                        </span>
                        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;background:{{ $sc[0] }};color:{{ $sc[1] }}">
                            {{ ucfirst(str_replace('_',' ',$req->status)) }}
                        </span>
                    </div>
                    <div class="maint-card-actions">
                        @if($req->status !== 'resolved')
                            <button onclick="openUpdateModal({{ $req->id }},'{{ $req->status }}','{{ addslashes($req->unit->name) }}','{{ addslashes($req->description) }}')"
                                    style="flex:1;padding:7px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:500">
                                Update status
                            </button>
                        @endif
                        <form method="POST" action="{{ route('maintenance.destroy', $req) }}"
                              onsubmit="return confirm('Delete this request?')" style="{{ $req->status!=='resolved'?'flex:0':'flex:1' }}">
                            @csrf @method('DELETE')
                            <button type="submit" style="width:100%;padding:7px {{ $req->status!=='resolved'?'10px':'0' }};background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Log Request Modal --}}
<div id="log-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Log a request</div>
            <button onclick="document.getElementById('log-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('maintenance.store') }}">
            @csrf
            <div style="display:grid;gap:13px;margin-bottom:18px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Unit</label>
                    <select name="unit_id" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select unit</option>
                        @foreach(\App\Models\Property::with('units')->get() as $property)
                            <optgroup label="{{ $property->name }}">
                                @foreach($property->units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} &ndash; {{ $unit->type }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Issue</label>
                    <textarea name="description" required rows="3" placeholder="Describe what needs attention..."
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px">Priority</label>
                    <div style="display:flex;gap:16px;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                            <input type="radio" name="priority" value="urgent" style="accent-color:#b91c1c">
                            <span style="color:#b91c1c;font-weight:500">Urgent</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                            <input type="radio" name="priority" value="normal" checked style="accent-color:#d97706">
                            Normal
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                            <input type="radio" name="priority" value="low" style="accent-color:#6b7280">
                            Low
                        </label>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional"
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Submit
                </button>
                <button type="button" onclick="document.getElementById('log-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Update Status Modal --}}
<div id="update-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:440px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div>
                <div style="font-size:15px;font-weight:500">Update request</div>
                <div style="font-size:12px;color:#8a8880;margin-top:2px" id="update-subtitle"></div>
            </div>
            <button onclick="document.getElementById('update-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" id="update-form">
            @csrf @method('PATCH')
            <div style="display:grid;gap:13px;margin-bottom:18px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Status</label>
                    <select name="status" id="update-status" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="open">Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Resolution notes</label>
                    <textarea name="resolution_notes" rows="3" placeholder="What was done..."
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save
                </button>
                <button type="button" onclick="document.getElementById('update-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateModal(id, status, unitName, description) {
    document.getElementById('update-modal').style.display = 'flex';
    document.getElementById('update-form').action = '/maintenance/' + id;
    document.getElementById('update-subtitle').textContent = 'Unit ' + unitName;
    document.getElementById('update-status').value = status;
}
</script>
</x-layouts.app>