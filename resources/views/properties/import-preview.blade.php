<x-layouts.app>
<style>
.preview-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.preview-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.summary-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.summary-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
}

.tbl-scroll {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 24px;
}

.tbl-scroll table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

.status-ready   { background: #dcfce7; color: #166534; }
.status-warning { background: #fef3c7; color: #92400e; }
.status-skip    { background: #f3f4f6; color: #6b7280; }
.status-error   { background: #fee2e2; color: #991b1b; }

.row-ready   { background: #fff; }
.row-warning { background: #fffbeb; }
.row-skip    { background: #f9fafb; opacity: 0.7; }
.row-error   { background: #fff5f5; }

.msg-list {
    margin-top: 4px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.msg-error   { font-size: 11px; color: #b91c1c; }
.msg-warning { font-size: 11px; color: #d97706; }
</style>

<div class="preview-wrap">

    <div class="preview-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('properties.show', $property) }}" style="color:#8a8880;text-decoration:none">
                    {{ $property->name }}
                </a>
                &rsaquo; Import preview
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">
                Review import
            </div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ count($rows) }} rows found &middot; {{ $property->name }}
            </div>
        </div>
    </div>

    {{-- Summary bar --}}
    <div class="summary-bar">
        @if($readyCount > 0)
            <div class="summary-pill" style="background:#dcfce7;color:#166534">
                <span style="font-size:16px">✓</span>
                {{ $readyCount }} ready to import
            </div>
        @endif
        @if($warningCount > 0)
            <div class="summary-pill" style="background:#fef3c7;color:#92400e">
                <span style="font-size:16px">⚠</span>
                {{ $warningCount }} with warnings
            </div>
        @endif
        @if($skipCount > 0)
            <div class="summary-pill" style="background:#f3f4f6;color:#6b7280">
                <span style="font-size:16px">–</span>
                {{ $skipCount }} will be skipped
            </div>
        @endif
        @if($errorCount > 0)
            <div class="summary-pill" style="background:#fee2e2;color:#991b1b">
                <span style="font-size:16px">✕</span>
                {{ $errorCount }} have errors
            </div>
        @endif
    </div>

    @if($errorCount > 0)
        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#92400e">
            Rows with errors will be automatically skipped during import. Fix them in your CSV and re-upload to include them.
        </div>
    @endif

    @if($readyCount + $warningCount === 0)
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:20px;margin-bottom:16px;text-align:center;font-size:13px;color:#991b1b">
            <div style="font-weight:600;margin-bottom:4px">Nothing to import</div>
            <div>All rows either have errors or are duplicates. Fix your CSV and try again.</div>
        </div>
    @endif

    {{-- Preview table --}}
    <div class="tbl-scroll">
        <table>
            <thead>
                <tr style="background:#faf9f7">
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">#</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Type</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Rent</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Phone</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Move-in</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Deposit</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="row-{{ $row['status'] }}" style="border-bottom:1px solid rgba(0,0,0,0.05)">
                        <td style="padding:10px 14px;font-size:12px;color:#8a8880">{{ $row['row_number'] }}</td>
                        <td style="padding:10px 14px;font-size:13px;font-weight:500">
                            {{ $row['unit_name'] ?: '—' }}
                        </td>
                        <td style="padding:10px 14px;font-size:13px;color:#8a8880">
                            {{ $row['unit_type'] ?: '—' }}
                        </td>
                        <td style="padding:10px 14px;font-size:13px;font-weight:500;text-align:right">
                            @if(is_numeric($row['rent_amount']))
                                {{ number_format(floatval($row['rent_amount'])) }}
                            @else
                                <span style="color:#b91c1c">{{ $row['rent_amount'] ?: '—' }}</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:13px">
                            @if($row['has_tenant'])
                                <div style="display:flex;align-items:center;gap:7px">
                                    <div style="width:24px;height:24px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                        {{ strtoupper(substr($row['first_name'],0,1).substr($row['last_name'],0,1)) }}
                                    </div>
                                    <span>{{ $row['first_name'] }} {{ $row['last_name'] }}</span>
                                </div>
                            @else
                                <span style="color:#8a8880;font-style:italic">Vacant</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:13px;font-family:monospace;color:#8a8880">
                            {{ $row['phone'] ?: '—' }}
                        </td>
                        <td style="padding:10px 14px;font-size:13px;color:#8a8880">
                            {{ $row['move_in_date'] ?: ($row['has_tenant'] ? 'Today' : '—') }}
                        </td>
                        <td style="padding:10px 14px;font-size:13px;text-align:right;color:#8a8880">
                            @if(!empty($row['deposit_paid']) && floatval($row['deposit_paid']) > 0)
                                {{ number_format(floatval($row['deposit_paid'])) }}
                                <div style="font-size:10px;text-transform:uppercase">{{ $row['deposit_method'] ?: 'cash' }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding:10px 14px">
                            <span class="status-{{ $row['status'] }}"
                                  style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500">
                                {{ match($row['status']) {
                                    'ready'   => 'Ready',
                                    'warning' => 'Warning',
                                    'skip'    => 'Skip',
                                    'error'   => 'Error',
                                    default   => ucfirst($row['status']),
                                } }}
                            </span>
                            @if(!empty($row['errors']) || !empty($row['warnings']))
                                <div class="msg-list">
                                    @foreach($row['errors'] as $err)
                                        <div class="msg-error">✕ {{ $err }}</div>
                                    @endforeach
                                    @foreach($row['warnings'] as $warn)
                                        <div class="msg-warning">⚠ {{ $warn }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Action buttons --}}
    @if($readyCount + $warningCount > 0)
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-size:13px;font-weight:500;margin-bottom:2px">
                    Ready to import {{ $readyCount + $warningCount }} {{ Str::plural('row', $readyCount + $warningCount) }}
                </div>
                <div style="font-size:12px;color:#8a8880">
                    {{ $readyCount + $warningCount }} {{ Str::plural('unit', $readyCount + $warningCount) }} will be created
                    @php
                        $tenantRows = collect($rows)->filter(fn($r) => in_array($r['status'],['ready','warning']) && $r['has_tenant'])->count();
                    @endphp
                    @if($tenantRows > 0)
                        &middot; {{ $tenantRows }} {{ Str::plural('tenant', $tenantRows) }} will be moved in
                    @endif
                    @if($skipCount > 0)
                        &middot; {{ $skipCount }} skipped
                    @endif
                    @if($errorCount > 0)
                        &middot; {{ $errorCount }} errors skipped
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <form method="POST" action="{{ route('properties.import.store', $property) }}">
                    @csrf
                    <button type="submit"
                            style="padding:8px 22px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                        Import {{ $readyCount + $warningCount }} {{ Str::plural('row', $readyCount + $warningCount) }}
                    </button>
                </form>
                <a href="{{ route('properties.show', $property) }}"
                   style="padding:8px 16px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none;white-space:nowrap">
                    Cancel
                </a>
            </div>
        </div>
    @else
        <div style="display:flex;gap:8px">
            <a href="{{ route('properties.show', $property) }}"
               style="padding:8px 16px;background:#1a6b52;color:#fff;border-radius:7px;font-size:13px;text-decoration:none">
                Back to property
            </a>
        </div>
    @endif
</div>
</x-layouts.app>