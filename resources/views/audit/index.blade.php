<x-layouts.app>
<style>
.audit-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.audit-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.audit-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.audit-filter-label {
    font-size: 10px;
    font-weight: 500;
    color: #8a8880;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.audit-filter-select,
.audit-filter-input {
    height: 34px;
    padding: 0 10px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 7px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    background: #fff;
}

.audit-log {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    overflow: hidden;
}

.audit-date-header {
    padding: 8px 18px;
    background: #f5f4f0;
    font-size: 11px;
    font-weight: 500;
    color: #8a8880;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.audit-entry {
    padding: 12px 18px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.audit-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f5f4f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 1px;
}

.audit-content { flex: 1; min-width: 0; }

.audit-meta {
    font-size: 11px;
    color: #8a8880;
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.audit-badge {
    font-size: 10px;
    color: #8a8880;
    background: #f5f4f0;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
    flex-shrink: 0;
}

.audit-property-badge {
    font-size: 10px;
    color: #0e7490;
    background: #e0f2fe;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
    flex-shrink: 0;
}

.pagination {
    margin-top: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    flex-wrap: wrap;
}

@media (max-width: 600px) {
    .audit-filters { gap: 8px; }
    .audit-filter-group { width: calc(50% - 4px); }
    .audit-filter-select,
    .audit-filter-input { width: 100%; }
    .audit-badge { display: none; }
    .audit-property-badge { display: none; }
}
</style>

<div class="audit-wrap">

    <div style="margin-bottom:24px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Audit trail</div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">Complete record of all actions in your account</div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('audit.index') }}" class="audit-filters">

        <div class="audit-filter-group">
            <label class="audit-filter-label">Property</label>
            <select name="property_id" class="audit-filter-select" onchange="this.form.submit()">
                <option value="">All properties</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ request('property_id')==$property->id?'selected':'' }}>
                        {{ $property->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="audit-filter-group">
            <label class="audit-filter-label">Event type</label>
            <select name="event" class="audit-filter-select">
                <option value="">All events</option>
                @foreach($eventGroups as $key => $label)
                    <option value="{{ $key }}" {{ request('event')===$key?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="audit-filter-group">
            <label class="audit-filter-label">User</label>
            <select name="user_id" class="audit-filter-select">
                <option value="">All users</option>
                <option value="system" {{ request('user_id')==='system'?'selected':'' }}>System</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id')==$user->id?'selected':'' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="audit-filter-group">
            <label class="audit-filter-label">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="audit-filter-input">
        </div>

        <div class="audit-filter-group" style="flex-direction:row;gap:6px;align-items:flex-end">
            <button type="submit"
                    style="height:34px;padding:0 14px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                Filter
            </button>
            @if(request()->hasAny(['event','user_id','date','property_id']))
                <a href="{{ route('audit.index') }}"
                   style="height:34px;padding:0 14px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;text-decoration:none;display:flex;align-items:center;white-space:nowrap">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Active property filter banner --}}
    @if(request('property_id') && $properties->firstWhere('id', request('property_id')))
        @php $filteredProperty = $properties->firstWhere('id', request('property_id')); @endphp
        <div style="background:#e0f2fe;border:1px solid #bae6fd;border-radius:8px;padding:9px 14px;margin-bottom:14px;font-size:13px;color:#0e7490;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span>🏠 Showing activity for <strong>{{ $filteredProperty->name }}</strong></span>
            <a href="{{ route('audit.index') }}" style="font-size:12px;color:#0e7490;text-decoration:none">
                Show all properties
            </a>
        </div>
    @endif

    @if($logs->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">📋</div>
            <div style="font-weight:500;margin-bottom:4px">No audit logs found</div>
            <div>Try adjusting your filters</div>
        </div>
    @else
        <div class="audit-log">
            @php $currentDate = null; @endphp
            @foreach($logs as $log)
                @php $logDate = $log->created_at->format('d F Y'); @endphp
                @if($logDate !== $currentDate)
                    @php $currentDate = $logDate; @endphp
                    <div class="audit-date-header">{{ $logDate }}</div>
                @endif
                <div class="audit-entry">
                    
                    <div class="audit-icon" style="background:{{ $log->eventColor() }}20">
                        @php
                            $icons = [
                                'person'   => 'M12 12c2.7 0 4-1.8 4-4s-1.3-4-4-4-4 1.8-4 4 1.3 4 4 4zm0 2c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4z',
                                'receipt'  => 'M18 2H6c-1.1 0-2 .9-2 2v20l4-2 4 2 4-2 4 2V4c0-1.1-.9-2-2-2zm-1 15H7v-2h10v2zm0-4H7v-2h10v2zm0-4H7V7h10v2z',
                                'payment'  => 'M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z',
                                'expense'  => 'M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z',
                                'wrench'   => 'M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z',
                                'utility'  => 'M7 2v11h3v9l7-12h-4l4-8z',
                                'sms'      => 'M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z',
                                'settings' => 'M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z',
                                'user'     => 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z',
                                'property' => 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z',
                                'unit'     => 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V7h10v2z',
                                'log'      => 'M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z',
                            ];
                            $path = $icons[$log->eventIcon()] ?? $icons['log'];
                        @endphp
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $log->eventColor() }}">
                            <path d="{{ $path }}"/>
                        </svg>
                    </div>
                    
                    <div class="audit-content">
                        <div style="font-size:13px;color:#111110;line-height:1.4">{{ $log->description }}</div>
                        <div class="audit-meta">
                            <span style="font-weight:500;color:{{ $log->eventColor() }}">{{ $log->actorName() }}</span>
                            <span>&middot;</span>
                            <span>{{ $log->created_at->format('g:i A') }}</span>
                            @if($log->ip_address)
                                <span>&middot;</span>
                                <span>{{ $log->ip_address }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
                        <div class="audit-badge">{{ $log->event }}</div>
                        @if($log->property)
                            <div class="audit-property-badge">🏠 {{ $log->property->name }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="pagination">
                @if($logs->onFirstPage())
                    <span style="padding:6px 12px;border-radius:6px;color:#c4c2be;border:1px solid rgba(0,0,0,0.07)">← Previous</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                       style="padding:6px 12px;border-radius:6px;color:#1a6b52;border:1px solid rgba(0,0,0,0.1);text-decoration:none">← Previous</a>
                @endif
                <span style="padding:6px 12px;font-size:12px;color:#8a8880">
                    Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                </span>
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                       style="padding:6px 12px;border-radius:6px;color:#1a6b52;border:1px solid rgba(0,0,0,0.1);text-decoration:none">Next →</a>
                @else
                    <span style="padding:6px 12px;border-radius:6px;color:#c4c2be;border:1px solid rgba(0,0,0,0.07)">Next →</span>
                @endif
            </div>
        @endif
    @endif
</div>
</x-layouts.app>