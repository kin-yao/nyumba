<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accounts — Nyumba Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#111110}
        .layout{display:flex;min-height:100vh}
        .main{margin-left:220px;flex:1;padding:32px 40px}
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap}
        .page-title{font-family:'DM Serif Display',serif;font-size:26px}
        .page-sub{font-size:13px;color:#8a8880;margin-top:4px}
        .filters{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
        .filters input,.filters select{height:36px;padding:0 12px;border:1px solid rgba(0,0,0,.1);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:#fff}
        .filters button{height:36px;padding:0 16px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif}
        .card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#8a8880;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,.07);font-weight:500}
        td{padding:11px 14px;font-size:13px;border-bottom:1px solid rgba(0,0,0,.04)}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafaf9}
        .badge{display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
        .badge-green{background:#e6f2ed;color:#1a6b52}
        .badge-amber{background:#fef3c7;color:#92400e}
        .badge-red{background:#fee2e2;color:#991b1b}
        .badge-gray{background:#f3f4f6;color:#4b5563}
        .btn{padding:7px 16px;font-size:13px;font-weight:500;border-radius:8px;text-decoration:none;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:6px}
        .btn-green{background:#1a6b52;color:#fff}
        .btn-sm{padding:4px 10px;font-size:12px;font-weight:500;border-radius:6px;text-decoration:none;cursor:pointer;border:1px solid rgba(0,0,0,.1);background:transparent;color:#8a8880;font-family:'DM Sans',sans-serif}
        .btn-sm:hover{background:#1a6b52;color:#fff;border-color:#1a6b52}
        a{text-decoration:none;color:inherit}
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar', ['active' => 'accounts'])
    <main class="main">

        <div class="page-header">
            <div>
                <div class="page-title">All accounts</div>
                <div class="page-sub">{{ $accounts->count() }} account(s) found</div>
            </div>
            <a href="{{ route('admin.accounts.create') }}" class="btn btn-green">
                + Create account
            </a>
        </div>

        @if(session('success'))
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#166534">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.accounts') }}" class="filters">
            <input type="text" name="search" placeholder="Search name, email or phone..." value="{{ request('search') }}" style="width:260px">
            <select name="plan">
                <option value="">All plans</option>
                @foreach(['explore','starter','growth','pro','enterprise'] as $p)
                    <option value="{{ $p }}" {{ request('plan') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">All statuses</option>
                <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>Active</option>
                <option value="trial"   {{ request('status') === 'trial'   ? 'selected' : '' }}>Trial</option>
                <option value="grace"   {{ request('status') === 'grace'   ? 'selected' : '' }}>Grace period</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit">Filter</button>
            @if(request()->hasAny(['search','plan','status']))
                <a href="{{ route('admin.accounts') }}" style="height:36px;padding:0 14px;display:flex;align-items:center;font-size:13px;color:#8a8880;border:1px solid rgba(0,0,0,.1);border-radius:8px;background:#fff">Clear</a>
            @endif
        </form>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Units</th>
                        <th>SMS credits</th>
                        <th>Expires</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <div style="font-weight:500">{{ $account->name }}</div>
                                <div style="font-size:11px;color:#8a8880">
                                    {{ $account->phone }}
                                    @if($account->email) &middot; {{ $account->email }} @endif
                                </div>
                            </td>
                            <td><span class="badge badge-gray">{{ ucfirst($account->plan) }}</span></td>
                            <td>
                                @if($account->isOnTrial())
                                    <span class="badge badge-amber">Trial &middot; {{ $account->trialDaysRemaining() }}d</span>
                                @elseif($account->isActive())
                                    <span class="badge badge-green">Active</span>
                                @elseif($account->isInGracePeriod())
                                    <span class="badge badge-amber">Grace &middot; {{ $account->graceDaysRemaining() }}d</span>
                                @else
                                    <span class="badge badge-red">Expired</span>
                                @endif
                            </td>
                            <td style="font-variant-numeric:tabular-nums">
                                {{ $account->units_count ?? 0 }} / {{ $account->unit_limit }}
                            </td>
                            <td style="font-variant-numeric:tabular-nums">{{ number_format($account->sms_credits) }}</td>
                            <td style="font-size:12px;color:#8a8880">{{ $account->plan_expires_at?->format('d M Y') ?? '—' }}</td>
                            <td style="font-size:12px;color:#8a8880">{{ $account->created_at->format('d M Y') }}</td>
                            <td><a href="{{ route('admin.account', $account) }}" class="btn-sm">Manage</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:48px;color:#8a8880">No accounts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>