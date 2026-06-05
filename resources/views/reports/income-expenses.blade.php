<x-layouts.app>
<style>
.report-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.ie-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 16px;
}

@media (max-width: 600px) {
    .ie-grid { grid-template-columns: 1fr; }
}
</style>

<div class="report-wrap">

    <div class="report-header">
        <div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('reports.index') }}" style="color:#8a8880;text-decoration:none">Reports</a>
                &rsaquo; Income vs Expenses
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Income vs Expenses</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </div>
        </div>
        <form method="GET" action="{{ route('reports.income-expenses') }}"
              style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex-shrink:0">
            <select name="month" onchange="this.form.submit()"
                    style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                        {{ \Carbon\Carbon::createFromDate($year,$m,1)->format('F') }}
                    </option>
                @endforeach
            </select>
            <input name="year" type="number" value="{{ $year }}" onchange="this.form.submit()"
                   style="height:34px;padding:0 10px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:80px">
        </form>
    </div>

    <div class="ie-grid">
        {{-- Income --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Income</div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(0,0,0,0.05);font-size:13px">
                <span style="color:#8a8880">Payments received</span>
                <span style="font-weight:500">{{ currency($payments) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding-top:10px;align-items:center">
                <span style="font-weight:500;font-size:13px">Total income</span>
                <span style="font-family:'DM Serif Display',serif;font-size:22px;color:#15803d">{{ currency($payments) }}</span>
            </div>
        </div>

        {{-- Expenses --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Expenses</div>
            @foreach($expensesByCategory as $category => $amount)
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(0,0,0,0.04);font-size:13px;gap:8px">
                    <span style="color:#8a8880">{{ ucfirst(str_replace('_',' ',$category)) }}</span>
                    <span style="white-space:nowrap">{{ currency($amount) }}</span>
                </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;padding-top:10px;align-items:center">
                <span style="font-weight:500;font-size:13px">Total expenses</span>
                <span style="font-family:'DM Serif Display',serif;font-size:22px;color:#b91c1c">{{ currency($totalExpenses) }}</span>
            </div>
        </div>
    </div>

    {{-- Net profit --}}
    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);border-left:4px solid {{ $netProfit>=0?'#15803d':'#b91c1c' }};padding:20px 24px">
        <div style="font-size:12px;color:#8a8880;margin-bottom:6px">
            Net {{ $netProfit >= 0 ? 'profit' : 'loss' }} for {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
        </div>
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(28px,5vw,40px);color:{{ $netProfit>=0?'#15803d':'#b91c1c' }}">
            {{ $netProfit < 0 ? '-' : '' }}{{ currency(abs($netProfit)) }}
        </div>
        @if($netProfit < 0)
            <div style="font-size:12px;color:#b91c1c;margin-top:4px">Expenses exceeded income this period</div>
        @else
            <div style="font-size:12px;color:#15803d;margin-top:4px">Profit margin: {{ $payments > 0 ? round(($netProfit/$payments)*100) : 0 }}%</div>
        @endif
    </div>
</div>
</x-layouts.app>