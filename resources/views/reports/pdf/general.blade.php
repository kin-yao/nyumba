<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $property->name }} — Report — {{ $periodLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111110;
            background: #ffffff;
            padding: 40px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            border-bottom: 2px solid #1a6b52;
            padding-bottom: 18px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .header-right { text-align: right; }
        .business-name { font-size: 14px; font-weight: bold; }
        .business-detail { font-size: 10px; color: #8a8880; margin-top: 2px; }
        .report-label { font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
        .report-sub { font-size: 12px; color: #1a6b52; font-weight: bold; margin-top: 4px; }
        .report-meta { font-size: 10px; color: #8a8880; margin-top: 3px; }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #111110;
            background: #f5f4f0;
            padding: 7px 10px;
            border-left: 3px solid #1a6b52;
            margin: 22px 0 10px;
        }
        .section-title:first-of-type { margin-top: 0; }

        .kpi-row { display: table; width: 100%; margin-bottom: 4px; }
        .kpi { display: table-cell; width: 25%; padding: 10px 8px; text-align: center; background: #faf9f7; border: 1px solid #ece9e2; }
        .kpi-value { font-size: 15px; font-weight: bold; }
        .kpi-label { font-size: 9px; color: #8a8880; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }
        .kpi-red .kpi-value { color: #b91c1c; }
        .kpi-green .kpi-value { color: #15803d; }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.data thead tr { background: #111110; color: #ffffff; }
        table.data thead th {
            padding: 7px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        table.data thead th.right { text-align: right; }
        table.data tbody tr { border-bottom: 1px solid #ece9e2; }
        table.data tbody tr:nth-child(even) { background: #faf9f7; }
        table.data tbody td { padding: 7px 10px; font-size: 11px; }
        table.data tbody td.right { text-align: right; }
        table.data tfoot td { padding: 8px 10px; font-size: 11px; font-weight: bold; border-top: 2px solid #111110; }
        table.data tfoot td.right { text-align: right; }

        .empty-note { font-size: 11px; color: #8a8880; padding: 10px; font-style: italic; }

        .footer { margin-top: 30px; padding-top: 14px; border-top: 1px solid #ece9e2; font-size: 9px; color: #8a8880; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <div class="business-name">{{ $account->name }}</div>
            @if($account->county)
                <div class="business-detail">{{ $account->county }}</div>
            @endif
            @if($account->phone)
                <div class="business-detail">{{ $account->phone }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="report-label">{{ $mode === 'yearly' ? 'Yearly Summary' : 'Monthly Report' }}</div>
            <div class="report-sub">{{ $property->name }}</div>
            <div class="report-meta">{{ $periodLabel }}</div>
            <div class="report-meta">Generated {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- ── Occupancy (current snapshot) ── --}}
    <div class="section-title">Occupancy</div>
    <div class="kpi-row">
        <div class="kpi"><div class="kpi-value">{{ $occupancy['total'] }}</div><div class="kpi-label">Total units</div></div>
        <div class="kpi kpi-green"><div class="kpi-value">{{ $occupancy['occupied'] }}</div><div class="kpi-label">Occupied</div></div>
        <div class="kpi"><div class="kpi-value">{{ $occupancy['vacant'] }}</div><div class="kpi-label">Vacant</div></div>
        <div class="kpi"><div class="kpi-value">{{ $occupancy['rate'] }}%</div><div class="kpi-label">Occupancy rate</div></div>
    </div>

    @if($mode === 'yearly')

        {{-- ── Rent roll & collections, month by month ── --}}
        <div class="section-title">Rent Roll &amp; Collections — {{ $periodLabel }}</div>
        <table class="data">
            <thead><tr><th>Month</th><th class="right">Expected</th><th class="right">Collected</th><th class="right">Rate</th></tr></thead>
            <tbody>
                @foreach($yearly['months'] as $m)
                <tr>
                    <td>{{ $m['label'] }}</td>
                    <td class="right">{{ currency($m['expected']) }}</td>
                    <td class="right">{{ currency($m['collected']) }}</td>
                    <td class="right">{{ $m['rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total for the year</td>
                    <td class="right">{{ currency($yearly['totals']['expected']) }}</td>
                    <td class="right">{{ currency($yearly['totals']['collected']) }}</td>
                    <td class="right">{{ $yearly['totals']['rate'] }}%</td>
                </tr>
            </tfoot>
        </table>

        {{-- ── Income vs expenses, month by month ── --}}
        <div class="section-title">Income vs Expenses — {{ $periodLabel }}</div>
        <table class="data">
            <thead><tr><th>Month</th><th class="right">Income</th><th class="right">Expenses</th><th class="right">Net</th></tr></thead>
            <tbody>
                @foreach($yearly['months'] as $m)
                <tr>
                    <td>{{ $m['label'] }}</td>
                    <td class="right">{{ currency($m['income']) }}</td>
                    <td class="right">{{ currency($m['expenses']) }}</td>
                    <td class="right">{{ currency($m['net']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total for the year</td>
                    <td class="right">{{ currency($yearly['totals']['income']) }}</td>
                    <td class="right">{{ currency($yearly['totals']['expenses']) }}</td>
                    <td class="right">{{ currency($yearly['totals']['net']) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- ── Utilities billed, month by month ── --}}
        <div class="section-title">Utilities Billed — {{ $periodLabel }}</div>
        <table class="data">
            <thead><tr><th>Month</th><th class="right">Charges billed</th></tr></thead>
            <tbody>
                @foreach($yearly['months'] as $m)
                <tr>
                    <td>{{ $m['label'] }}</td>
                    <td class="right">{{ currency($m['utilities']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td>Total for the year</td><td class="right">{{ currency($yearly['totals']['utilities']) }}</td></tr>
            </tfoot>
        </table>

    @else

        {{-- ── Rent roll ── --}}
        <div class="section-title">Rent Roll — {{ $periodLabel }}</div>
        <div class="kpi-row">
            <div class="kpi"><div class="kpi-value">{{ currency($rentRoll['totalExpected']) }}</div><div class="kpi-label">Expected</div></div>
            <div class="kpi kpi-green"><div class="kpi-value">{{ currency($rentRoll['totalCollected']) }}</div><div class="kpi-label">Collected</div></div>
            <div class="kpi kpi-red"><div class="kpi-value">{{ currency($rentRoll['totalOutstanding']) }}</div><div class="kpi-label">Outstanding</div></div>
            <div class="kpi"><div class="kpi-value">{{ $rentRoll['collectionRate'] }}%</div><div class="kpi-label">Collection rate</div></div>
        </div>

        @if(count($rentRoll['rows']))
        <table class="data">
            <thead><tr><th>Unit</th><th>Tenant</th><th class="right">Expected</th><th class="right">Collected</th></tr></thead>
            <tbody>
                @foreach($rentRoll['rows'] as $row)
                <tr>
                    <td>{{ $row['unit']->name }}</td>
                    <td>{{ $row['tenant']->full_name }}</td>
                    <td class="right">{{ currency($row['expected']) }}</td>
                    <td class="right">{{ currency($row['collected']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-note">No active leases for this property.</div>
        @endif

        {{-- ── Collections ── --}}
        <div class="section-title">Collections — {{ $periodLabel }}</div>
        <div class="kpi-row">
            <div class="kpi kpi-green"><div class="kpi-value">{{ currency($collections['totalCollected']) }}</div><div class="kpi-label">Total collected</div></div>
            <div class="kpi"><div class="kpi-value">{{ $collections['payments']->count() }}</div><div class="kpi-label">Payments received</div></div>
        </div>

        @if($collections['byMethod']->count())
        <table class="data">
            <thead><tr><th>Method</th><th class="right">Count</th><th class="right">Amount</th></tr></thead>
            <tbody>
                @foreach($collections['byMethod'] as $method => $row)
                <tr>
                    <td style="text-transform:capitalize">{{ $method ?: 'Unspecified' }}</td>
                    <td class="right">{{ $row['count'] }}</td>
                    <td class="right">{{ currency($row['amount']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-note">No payments recorded in this period.</div>
        @endif

        {{-- ── Income vs expenses ── --}}
        <div class="section-title">Income vs Expenses — {{ $periodLabel }}</div>
        <div class="kpi-row">
            <div class="kpi kpi-green"><div class="kpi-value">{{ currency($incomeExpenses['totalIncome']) }}</div><div class="kpi-label">Income</div></div>
            <div class="kpi kpi-red"><div class="kpi-value">{{ currency($incomeExpenses['totalExpenses']) }}</div><div class="kpi-label">Expenses</div></div>
            <div class="kpi"><div class="kpi-value">{{ currency($incomeExpenses['netProfit']) }}</div><div class="kpi-label">Net profit</div></div>
        </div>

        @if($incomeExpenses['byCategory']->count())
        <table class="data">
            <thead><tr><th>Category</th><th class="right">Amount</th></tr></thead>
            <tbody>
                @foreach($incomeExpenses['byCategory'] as $category => $amount)
                <tr>
                    <td style="text-transform:capitalize">{{ $category ?: 'Uncategorised' }}</td>
                    <td class="right">{{ currency($amount) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-note">No expenses recorded in this period.</div>
        @endif

        {{-- ── Utilities ── --}}
        <div class="section-title">Utilities — {{ $periodLabel }}</div>
        <div class="kpi-row">
            <div class="kpi kpi-green"><div class="kpi-value">{{ currency($utilities['totalCharge']) }}</div><div class="kpi-label">Total billed</div></div>
            @if($utilities['missing'] > 0)
            <div class="kpi kpi-red"><div class="kpi-value">{{ $utilities['missing'] }}</div><div class="kpi-label">Missing readings</div></div>
            @endif
        </div>

        @if(count($utilities['rows']))
        <table class="data">
            <thead><tr><th>Unit</th><th>Tenant</th><th>Utility</th><th class="right">Consumed</th><th class="right">Charge</th></tr></thead>
            <tbody>
                @foreach($utilities['rows'] as $row)
                <tr>
                    <td>{{ $row['unit'] }}</td>
                    <td>{{ $row['tenant'] }}</td>
                    <td>{{ $row['utility_name'] }}</td>
                    <td class="right">{{ number_format($row['consumed'], 1) }}</td>
                    <td class="right">{{ currency($row['charge']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-note">No utility readings recorded in this period.</div>
        @endif

    @endif

    {{-- ── Outstanding balances (current) ── --}}
    <div class="section-title">Outstanding Balances <span style="font-weight:normal;font-size:9px;color:#8a8880;text-transform:none">(current, as of {{ now()->format('d M Y') }})</span></div>
    @if($outstanding['leases']->count())
    <table class="data">
        <thead><tr><th>Tenant</th><th class="right">Balance</th></tr></thead>
        <tbody>
            @foreach($outstanding['leases'] as $row)
            <tr>
                <td>{{ $row['tenant']->full_name }}</td>
                <td class="right">{{ currency($row['balance']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td>Total outstanding</td><td class="right">{{ currency($outstanding['total']) }}</td></tr>
        </tfoot>
    </table>
    @else
    <div class="empty-note">No outstanding balances.</div>
    @endif

    {{-- ── Deposits (current) ── --}}
    <div class="section-title">Deposits Held <span style="font-weight:normal;font-size:9px;color:#8a8880;text-transform:none">(current, as of {{ now()->format('d M Y') }})</span></div>
    <div class="kpi-row">
        <div class="kpi"><div class="kpi-value">{{ currency($deposits['totalRequired']) }}</div><div class="kpi-label">Required</div></div>
        <div class="kpi kpi-green"><div class="kpi-value">{{ currency($deposits['totalHeld']) }}</div><div class="kpi-label">Held</div></div>
        <div class="kpi kpi-red"><div class="kpi-value">{{ currency($deposits['totalOutstanding']) }}</div><div class="kpi-label">Outstanding</div></div>
    </div>

    <div class="footer">Generated by Nyumba for {{ $account->name }} — {{ $property->name }}</div>

</body>
</html>