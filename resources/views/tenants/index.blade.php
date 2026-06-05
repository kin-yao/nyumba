<x-layouts.app>
<style>
.ten-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.ten-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

/* On mobile, tenant rows become cards */
.ten-table { display: block; }

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
    min-width: 580px;
}

/* Mobile tenant cards */
.ten-cards { display: none; }
.ten-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.ten-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}
.ten-card-meta {
    display: flex;
    gap: 8px;
    margin-top: 6px;
    flex-wrap: wrap;
}
.ten-card-tag {
    font-size: 11px;
    color: #8a8880;
    background: #f5f4f0;
    padding: 2px 8px;
    border-radius: 20px;
}
.ten-card-actions {
    display: flex;
    gap: 6px;
    margin-top: 10px;
}

@media (max-width: 640px) {
    .tbl-scroll { display: none; }
    .ten-cards  { display: block; }
}
</style>

<div class="ten-wrap">

    <div class="ten-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Tenants</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ $tenants->count() }} active {{ Str::plural('tenant', $tenants->count()) }}
                @if($archivedTenants->count() > 0)
                    &middot; {{ $archivedTenants->count() }} archived
                @endif
            </div>
        </div>
        <a href="{{ route('tenants.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;text-decoration:none;white-space:nowrap;flex-shrink:0">
            + Move in tenant
        </a>
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

    {{-- Tabs --}}
    <div style="display:flex;gap:4px;margin-bottom:16px;border-bottom:1px solid rgba(0,0,0,0.07)">
        <button onclick="showTab('active',this)" id="tab-active"
                style="padding:8px 16px;font-size:13px;font-weight:500;border:none;background:transparent;cursor:pointer;font-family:'DM Sans',sans-serif;color:#1a6b52;border-bottom:2px solid #1a6b52;margin-bottom:-1px">
            Active ({{ $tenants->count() }})
        </button>
        <button onclick="showTab('archived',this)" id="tab-archived"
                style="padding:8px 16px;font-size:13px;font-weight:500;border:none;background:transparent;cursor:pointer;font-family:'DM Sans',sans-serif;color:#8a8880;border-bottom:2px solid transparent;margin-bottom:-1px">
            Archived ({{ $archivedTenants->count() }})
        </button>
    </div>

    {{-- Active tenants --}}
    <div id="panel-active">
        @if($tenants->isEmpty())
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
                <div style="font-size:36px;margin-bottom:12px">👤</div>
                <div style="font-weight:500;margin-bottom:4px">No active tenants</div>
                <div>Move in your first tenant to get started</div>
            </div>
        @else
            {{-- Desktop table --}}
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Unit</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Phone</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Rent/mo</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Since</th>
                            <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            @php
                                $lease    = $tenant->leases->first();
                                $unit     = $lease?->unit;
                                $property = $unit?->property;
                                $hasActiveLease = $tenant->leases->where('status','active')->count() > 0;
                            @endphp
                            <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                                <td style="padding:11px 14px">
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:28px;height:28px;border-radius:50%;background:#e6f2ed;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                            {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:500;font-size:13px">{{ $tenant->full_name }}</div>
                                            <div style="font-size:11px;color:#8a8880">Since {{ $lease?->move_in_date?->format('M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 14px;font-size:13px">{{ $unit?->name ?? '-' }}</td>
                                <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $property?->name ?? '-' }}</td>
                                <td style="padding:11px 14px;font-size:13px">{{ $tenant->phone }}</td>
                                <td style="padding:11px 14px;font-size:13px;font-weight:500">{{ currency($lease?->monthly_rent ?? 0) }}</td>
                                <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $lease?->move_in_date?->format('d M Y') }}</td>
                                <td style="padding:11px 14px;text-align:right">
                                    <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end">
                                        <a href="{{ route('tenants.show', $tenant) }}"
                                           style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:6px;font-size:12px;text-decoration:none">
                                            View
                                        </a>
                                        @if(!$hasActiveLease)
                                            <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                                  onsubmit="return confirm('Archive {{ $tenant->first_name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                                    Archive
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="ten-cards">
                @foreach($tenants as $tenant)
                    @php
                        $lease    = $tenant->leases->first();
                        $unit     = $lease?->unit;
                        $property = $unit?->property;
                        $hasActiveLease = $tenant->leases->where('status','active')->count() > 0;
                    @endphp
                    <div class="ten-card">
                        <div class="ten-card-row">
                            <div style="display:flex;align-items:center;gap:10px;min-width:0">
                                <div style="width:34px;height:34px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                    {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                </div>
                                <div style="min-width:0">
                                    <div style="font-weight:500;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $tenant->full_name }}</div>
                                    <div style="font-size:11px;color:#8a8880">{{ $tenant->phone }}</div>
                                </div>
                            </div>
                            <div style="font-size:13px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                {{ currency($lease?->monthly_rent ?? 0) }}
                            </div>
                        </div>
                        <div class="ten-card-meta">
                            @if($unit)
                                <span class="ten-card-tag">Unit {{ $unit->name }}</span>
                            @endif
                            @if($property)
                                <span class="ten-card-tag">{{ $property->name }}</span>
                            @endif
                            @if($lease?->move_in_date)
                                <span class="ten-card-tag">Since {{ $lease->move_in_date->format('M Y') }}</span>
                            @endif
                        </div>
                        <div class="ten-card-actions">
                            <a href="{{ route('tenants.show', $tenant) }}"
                               style="flex:1;text-align:center;padding:7px;background:#f5f4f0;color:#111110;border-radius:7px;font-size:12px;text-decoration:none;font-weight:500">
                                View profile
                            </a>
                            @if(!$hasActiveLease)
                                <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                      onsubmit="return confirm('Archive {{ $tenant->first_name }}?')" style="flex:1">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:100%;padding:7px;background:#fff;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                        Archive
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Archived tenants --}}
    <div id="panel-archived" style="display:none">
        @if($archivedTenants->isEmpty())
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
                <div style="font-size:36px;margin-bottom:12px">📁</div>
                <div style="font-weight:500;margin-bottom:4px">No archived tenants</div>
                <div>Archived tenants will appear here</div>
            </div>
        @else
            {{-- Desktop table --}}
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Tenant</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Last unit</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Phone</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Archived</th>
                            <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedTenants as $tenant)
                            @php
                                $lease    = $tenant->leases->sortByDesc('created_at')->first();
                                $unit     = $lease?->unit;
                                $property = $unit?->property;
                            @endphp
                            <tr style="border-bottom:1px solid rgba(0,0,0,0.05);opacity:0.7">
                                <td style="padding:11px 14px">
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:28px;height:28px;border-radius:50%;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#6b7280;flex-shrink:0">
                                            {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:500;font-size:13px">{{ $tenant->full_name }}</div>
                                            <div style="font-size:11px;color:#8a8880">ID: {{ $tenant->id_number ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 14px;font-size:13px">{{ $unit?->name ?? '-' }}</td>
                                <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $property?->name ?? '-' }}</td>
                                <td style="padding:11px 14px;font-size:13px">{{ $tenant->phone }}</td>
                                <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $tenant->deleted_at?->format('d M Y') }}</td>
                                <td style="padding:11px 14px;text-align:right">
                                    <form method="POST" action="{{ route('tenants.restore', $tenant->id) }}">
                                        @csrf
                                        <button type="submit" style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#1a6b52;border:1px solid rgba(26,107,82,0.3);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="ten-cards">
                @foreach($archivedTenants as $tenant)
                    @php
                        $lease    = $tenant->leases->sortByDesc('created_at')->first();
                        $unit     = $lease?->unit;
                        $property = $unit?->property;
                    @endphp
                    <div class="ten-card" style="opacity:0.8">
                        <div class="ten-card-row">
                            <div style="display:flex;align-items:center;gap:10px;min-width:0">
                                <div style="width:34px;height:34px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#6b7280;flex-shrink:0">
                                    {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                                </div>
                                <div style="min-width:0">
                                    <div style="font-weight:500;font-size:13px">{{ $tenant->full_name }}</div>
                                    <div style="font-size:11px;color:#8a8880">{{ $tenant->phone }}</div>
                                </div>
                            </div>
                            <div style="font-size:11px;color:#8a8880;flex-shrink:0">
                                {{ $tenant->deleted_at?->format('d M Y') }}
                            </div>
                        </div>
                        <div class="ten-card-meta">
                            @if($unit) <span class="ten-card-tag">Last: Unit {{ $unit->name }}</span> @endif
                            @if($property) <span class="ten-card-tag">{{ $property->name }}</span> @endif
                        </div>
                        <div class="ten-card-actions">
                            <form method="POST" action="{{ route('tenants.restore', $tenant->id) }}" style="flex:1">
                                @csrf
                                <button type="submit" style="width:100%;padding:7px;background:#e6f2ed;color:#1a6b52;border:none;border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:500">
                                    Restore
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
function showTab(tab, btn) {
    document.getElementById('panel-active').style.display   = tab === 'active'   ? 'block' : 'none';
    document.getElementById('panel-archived').style.display = tab === 'archived' ? 'block' : 'none';

    ['tab-active','tab-archived'].forEach(id => {
        document.getElementById(id).style.color        = '#8a8880';
        document.getElementById(id).style.borderBottom = '2px solid transparent';
    });
    btn.style.color        = '#1a6b52';
    btn.style.borderBottom = '2px solid #1a6b52';
}
@if(session('show_archived'))
    showTab('archived', document.getElementById('tab-archived'));
@endif
</script>
</x-layouts.app>