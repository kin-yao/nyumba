<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#111110}
        .layout{display:flex;min-height:100vh}
        .main{margin-left:220px;flex:1;padding:32px 40px;min-height:100vh}
        .page-title{font-family:'DM Serif Display',serif;font-size:26px;margin-bottom:4px}
        .page-sub{font-size:13px;color:#8a8880;margin-bottom:28px}
        .kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px}
        .kpi{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:18px}
        .kpi-label{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:8px}
        .kpi-value{font-family:'DM Serif Display',serif;font-size:28px;line-height:1}
        .kpi-sub{font-size:11px;color:#8a8880;margin-top:5px}
        .health-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
        .health-card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:16px}
        .health-label{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:6px}
        .health-value{font-family:'DM Serif Display',serif;font-size:22px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
        .card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:22px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.06)}
        table{width:100%;border-collapse:collapse}
        th{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#8a8880;padding:8px 10px;text-align:left;border-bottom:1px solid rgba(0,0,0,.07);font-weight:500}
        td{padding:10px 10px;font-size:13px;border-bottom:1px solid rgba(0,0,0,.04)}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafaf9}
        .badge{display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
        .badge-green{background:#e6f2ed;color:#1a6b52}
        .badge-amber{background:#fef3c7;color:#92400e}
        .badge-red{background:#fee2e2;color:#991b1b}
        .badge-gray{background:#f3f4f6;color:#4b5563}
        .plan-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(0,0,0,.05)}
        .plan-row:last-child{border-bottom:none}
        .btn-sm{padding:4px 10px;font-size:12px;font-weight:500;border-radius:6px;text-decoration:none;background:#1a6b52;color:#fff;border:none;cursor:pointer;font-family:'DM Sans',sans-serif}
        a{color:inherit;text-decoration:none}
        .progress-bar{height:5px;background:#ece9e2;border-radius:3px;overflow:hidden;margin-top:6px}
        .progress-fill{height:100%;border-radius:3px;transition:width .3s}
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar', ['active' => 'dashboard'])
    <main class="main">

        @if(session('success'))
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:13px;color:#166534">
                {{ session('success') }}
            </div>
        @endif

        <div class="page-title">Admin dashboard</div>
        <div class="page-sub">{{ now()->format('l, d F Y') }}</div>

        {{-- Account status KPIs --}}
        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-label">Total accounts</div>
                <div class="kpi-value">{{ $totalAccounts }}</div>
                <div class="kpi-sub">{{ $totalUsers }} total users</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Active (paid)</div>
                <div class="kpi-value" style="color:#1a6b52">{{ $activeAccounts }}</div>
                <div class="kpi-sub">Paid subscriptions</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">On trial</div>
                <div class="kpi-value" style="color:#d97706">{{ $trialAccounts }}</div>
                <div class="kpi-sub">Explore plan</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Grace period</div>
                <div class="kpi-value" style="color:#d97706">{{ $graceAccounts }}</div>
                <div class="kpi-sub">Need renewal</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Expired</div>
                <div class="kpi-value" style="color:#b91c1c">{{ $expiredAccounts }}</div>
                <div class="kpi-sub">No access</div>
            </div>
        </div>

        {{-- System health --}}
        <div class="health-grid">
            <div class="health-card">
                <div class="health-label">Total units</div>
                <div class="health-value">{{ number_format($totalUnits) }}</div>
                <div style="font-size:11px;color:#8a8880;margin-top:4px">Across all properties</div>
            </div>
            <div class="health-card">
                <div class="health-label">Total properties</div>
                <div class="health-value">{{ number_format($totalProperties) }}</div>
                <div style="font-size:11px;color:#8a8880;margin-top:4px">All accounts</div>
            </div>
            <div class="health-card">
                <div class="health-label">SMS credits in system</div>
                <div class="health-value">{{ number_format($totalSmsCredits) }}</div>
                <div style="font-size:11px;color:#8a8880;margin-top:4px">Across all accounts</div>
            </div>
            <div class="health-card">
                <div class="health-label">Monthly revenue</div>
                <div class="health-value" style="color:#1a6b52">{{ currency($totalMrr) }}</div>
                <div style="font-size:11px;color:#8a8880;margin-top:4px">Active paid plans</div>
            </div>
        </div>

        <div class="grid-2">
            {{-- Recent signups --}}
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center" class="card-title">
                    <span>Recent signups</span>
                    <a href="{{ route('admin.accounts') }}" style="font-size:12px;color:#1a6b52">View all</a>
                </div>
                <table>
                    <thead><tr>
                        <th>Account</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                        @foreach($recentAccounts as $account)
                            <tr>
                                <td>
                                    <div style="font-weight:500;font-size:13px">{{ $account->name }}</div>
                                    <div style="font-size:11px;color:#8a8880">{{ $account->phone }}</div>
                                </td>
                                <td><span class="badge badge-gray">{{ ucfirst($account->plan) }}</span></td>
                                <td>
                                    @if($account->isOnTrial())
                                        <span class="badge badge-amber">Trial</span>
                                    @elseif($account->isActive())
                                        <span class="badge badge-green">Active</span>
                                    @elseif($account->isInGracePeriod())
                                        <span class="badge badge-amber">Grace</span>
                                    @else
                                        <span class="badge badge-red">Expired</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.account', $account) }}" class="btn-sm">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Revenue by plan --}}
            <div class="card">
                <div class="card-title">Revenue by plan</div>
                @foreach($revenueByPlan as $plan => $data)
                    <div class="plan-row">
                        <div>
                            <div style="font-size:13px;font-weight:500">{{ ucfirst($plan) }}</div>
                            <div style="font-size:11px;color:#8a8880">{{ $data['count'] }} active account(s)</div>
                        </div>
                        <div style="font-size:15px;font-weight:500;color:#1a6b52">{{ currency($data['revenue']) }}</div>
                    </div>
                @endforeach
                <div style="border-top:2px solid rgba(0,0,0,.08);margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;align-items:center">
                    <div style="font-weight:500">Total MRR</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:#1a6b52">{{ currency($totalMrr) }}</div>
                </div>
            </div>
        </div>

        {{-- Accounts approaching unit limit --}}
        @if($approachingLimit->isNotEmpty())
            <div class="card">
                <div class="card-title">Accounts approaching unit limit (≥80% full)</div>
                <table>
                    <thead><tr>
                        <th>Account</th>
                        <th>Plan</th>
                        <th>Units used</th>
                        <th>Capacity</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                        @foreach($approachingLimit as $item)
                            <tr>
                                <td style="font-weight:500">{{ $item['account']->name }}</td>
                                <td><span class="badge badge-gray">{{ ucfirst($item['account']->plan) }}</span></td>
                                <td>{{ $item['used'] }} / {{ $item['limit'] }}</td>
                                <td style="min-width:120px">
                                    <div style="font-size:12px;color:{{ $item['percentage'] >= 100 ? '#b91c1c' : '#d97706' }}">{{ $item['percentage'] }}%</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:{{ min($item['percentage'], 100) }}%;background:{{ $item['percentage'] >= 100 ? '#b91c1c' : '#d97706' }}"></div>
                                    </div>
                                </td>
                                <td><a href="{{ route('admin.account', $item['account']) }}" class="btn-sm">Manage</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </main>
</div>
</body>
</html>