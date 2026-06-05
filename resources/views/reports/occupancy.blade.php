<x-layouts.app>
<style>
.report-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.occ-kpi {
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
    min-width: 480px;
}

/* Mobile cards */
.occ-cards { display: none; }
.occ-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.occ-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}
.occ-card-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 10px;
    text-align: center;
}

@media (max-width: 700px) {
    .occ-kpi { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .tbl-scroll { display: none; }
    .occ-cards  { display: block; }
}
</style>

<div class="report-wrap">

    <div style="margin-bottom:24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
            <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
            &rsaquo; Occupancy
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Occupancy report</div>
    </div>

    {{-- KPI strip --}}
    <div class="occ-kpi">
        @foreach([
            ['Total units',    $totalUnits,    null],
            ['Occupied',       $totalOccupied, '#15803d'],
            ['Vacant',         $totalVacant,   $totalVacant>0?'#b91c1c':null],
            ['Occupancy rate', $overallRate.'%', $overallRate>=80?'#15803d':'#b91c1c'],
        ] as [$label, $value, $color])
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 20px">
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">{{ $label }}</div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,3vw,27px);color:{{ $color ?? '#111110' }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Desktop table --}}
    <div class="tbl-scroll">
        <table>
            <thead>
                <tr>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Type</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:center;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Total</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:center;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Occupied</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:center;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Vacant</th>
                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $row)
                    <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                        <td style="padding:11px 14px;font-size:13px;font-weight:500">{{ $row['property']->name }}</td>
                        <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ ucfirst($row['property']->type) }}</td>
                        <td style="padding:11px 14px;font-size:13px;text-align:center">{{ $row['total'] }}</td>
                        <td style="padding:11px 14px;font-size:13px;text-align:center;color:#15803d;font-weight:500">{{ $row['occupied'] }}</td>
                        <td style="padding:11px 14px;font-size:13px;text-align:center;color:{{ $row['vacant']>0?'#b91c1c':'#8a8880' }};font-weight:{{ $row['vacant']>0?'500':'400' }}">{{ $row['vacant'] }}</td>
                        <td style="padding:11px 14px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:80px;height:4px;background:#ece9e2;border-radius:2px;overflow:hidden;flex-shrink:0">
                                    <div style="height:100%;background:#1a6b52;border-radius:2px;width:{{ $row['rate'] }}%"></div>
                                </div>
                                <span style="font-size:12px;font-weight:500;color:{{ $row['rate']>=80?'#15803d':'#b91c1c' }};white-space:nowrap">{{ $row['rate'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="occ-cards">
        @foreach($summary as $row)
            <div class="occ-card">
                <div class="occ-card-top">
                    <div>
                        <div style="font-weight:500;font-size:13px">{{ $row['property']->name }}</div>
                        <div style="font-size:11px;color:#8a8880">{{ ucfirst($row['property']->type) }}</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-family:'DM Serif Display',serif;font-size:20px;color:{{ $row['rate']>=80?'#15803d':'#b91c1c' }}">{{ $row['rate'] }}%</div>
                        <div style="font-size:11px;color:#8a8880">occupancy</div>
                    </div>
                </div>
                <div class="occ-card-stats">
                    <div style="background:#f5f4f0;border-radius:7px;padding:8px 4px">
                        <div style="font-size:18px;font-weight:600">{{ $row['total'] }}</div>
                        <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em">Total</div>
                    </div>
                    <div style="background:#f0fdf4;border-radius:7px;padding:8px 4px">
                        <div style="font-size:18px;font-weight:600;color:#15803d">{{ $row['occupied'] }}</div>
                        <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em">Occupied</div>
                    </div>
                    <div style="background:{{ $row['vacant']>0?'#fff1f2':'#f5f4f0' }};border-radius:7px;padding:8px 4px">
                        <div style="font-size:18px;font-weight:600;color:{{ $row['vacant']>0?'#b91c1c':'#8a8880' }}">{{ $row['vacant'] }}</div>
                        <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em">Vacant</div>
                    </div>
                </div>
                <div style="height:5px;background:#ece9e2;border-radius:3px;overflow:hidden">
                    <div style="height:100%;background:#1a6b52;border-radius:3px;width:{{ $row['rate'] }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</x-layouts.app>