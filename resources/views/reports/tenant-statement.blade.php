<x-layouts.app>
<style>
.report-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.stmt-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.tbl-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.tbl-scroll table {
    width: 100%;
    border-collapse: collapse;
    min-width: 460px;
}
</style>

<div class="report-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
            &rsaquo; Tenant statement
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Tenant statement</div>
    </div>

    {{-- Tenant picker --}}
    <div style="max-width:600px;margin-bottom:20px">
        <form method="GET" action="{{ route('reports.tenant-statement') }}">
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <select name="tenant_id" required
                        style="flex:1;min-width:200px;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <option value="" disabled selected>Select a tenant</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ isset($tenant)&&$tenant->id==$t->id?'selected':'' }}>
                            {{ $t->full_name }}
                            @if($t->activeLease) &ndash; {{ $t->activeLease->unit->name ?? '' }} @endif
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        style="padding:0 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;height:36px;white-space:nowrap">
                    View statement
                </button>
            </div>
        </form>
    </div>

    @if($tenant && $ledger->isNotEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);max-width:700px;overflow:hidden">

            {{-- Statement header --}}
            <div style="padding:18px 20px;border-bottom:1px solid rgba(0,0,0,0.07)">
                <div class="stmt-header-bar">
                    <div>
                        <div style="font-size:15px;font-weight:500">{{ $tenant->full_name }}</div>
                        <div style="font-size:12px;color:#8a8880;margin-top:2px">
                            {{ $tenant->leases->where('status','active')->first()?->unit?->name }}
                            &middot;
                            {{ $tenant->leases->where('status','active')->first()?->unit?->property?->name }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:11px;color:#8a8880">Current balance</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,3vw,22px);color:{{ $balance>0?'#b91c1c':'#15803d' }}">
                            {{ currency(abs($balance)) }}
                        </div>
                        <div style="font-size:11px;color:#8a8880">
                            {{ $balance>0?'Owes':($balance<0?'In credit':'Fully paid') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ledger table --}}
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Description</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Ref</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Charged</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledger as $entry)
                            <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                                <td style="padding:10px 14px;font-size:12px;color:#8a8880;white-space:nowrap">
                                    {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                                </td>
                                <td style="padding:10px 14px;font-size:13px">{{ $entry['description'] }}</td>
                                <td style="padding:10px 14px;font-size:11px;font-family:monospace;color:#8a8880">{{ $entry['reference'] ?? '-' }}</td>
                                <td style="padding:10px 14px;font-size:13px;text-align:right;font-weight:500;white-space:nowrap">
                                    @if($entry['charged']) {{ currency($entry['charged']) }} @endif
                                </td>
                                <td style="padding:10px 14px;font-size:13px;text-align:right;font-weight:500;color:#15803d;white-space:nowrap">
                                    @if($entry['paid']) {{ currency($entry['paid']) }} @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tenant && $ledger->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:40px;text-align:center;color:#8a8880;font-size:13px;max-width:700px">
            No transactions found for {{ $tenant->full_name }}.
        </div>
    @endif
</div>
</x-layouts.app>