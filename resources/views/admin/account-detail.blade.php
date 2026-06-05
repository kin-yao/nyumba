<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $account->name }} — Nyumba Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#111110}
        .layout{display:flex;min-height:100vh}
        .main{margin-left:220px;flex:1;padding:32px 40px}
        .back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#8a8880;text-decoration:none;margin-bottom:20px}
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap}
        .page-title{font-family:'DM Serif Display',serif;font-size:26px}
        .page-sub{font-size:13px;color:#8a8880;margin-top:4px}
        .header-actions{display:flex;gap:8px;flex-wrap:wrap}
        .btn{padding:8px 16px;font-size:13px;font-weight:500;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;border:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
        .btn-green{background:#1a6b52;color:#fff}
        .btn-blue{background:#1e40af;color:#fff}
        .btn-red{background:#fee2e2;color:#b91c1c;border:1px solid rgba(185,28,28,.2)}
        .btn-gray{background:#fff;color:#374151;border:1px solid rgba(0,0,0,.1)}
        .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
        .kpi{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:16px}
        .kpi-label{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:6px}
        .kpi-value{font-family:'DM Serif Display',serif;font-size:24px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
        .card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:22px;margin-bottom:20px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.06)}
        .field-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(0,0,0,.05);font-size:13px}
        .field-row:last-child{border-bottom:none}
        .field-label{color:#8a8880}
        .field-value{font-weight:500}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input,select{width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:#fff}
        input:focus,select:focus{border-color:#1a6b52}
        .form-group{margin-bottom:14px}
        .badge{display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
        .badge-green{background:#e6f2ed;color:#1a6b52}
        .badge-amber{background:#fef3c7;color:#92400e}
        .badge-red{background:#fee2e2;color:#991b1b}
        .badge-gray{background:#f3f4f6;color:#4b5563}
        table{width:100%;border-collapse:collapse}
        th{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#8a8880;padding:8px 0;text-align:left;border-bottom:1px solid rgba(0,0,0,.07);font-weight:500}
        td{padding:9px 0;font-size:13px;border-bottom:1px solid rgba(0,0,0,.04)}
        tr:last-child td{border-bottom:none}
        a{text-decoration:none;color:inherit}
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar', ['active' => 'accounts'])
    <main class="main">

        <a href="{{ route('admin.accounts') }}" class="back-link">← All accounts</a>

        @foreach(['success','error'] as $type)
            @if(session($type))
                <div style="background:{{ $type==='success'?'#dcfce7':'#fee2e2' }};border:1px solid {{ $type==='success'?'#86efac':'#fca5a5' }};border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:{{ $type==='success'?'#166534':'#991b1b' }}">
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        <div class="page-header">
            <div>
                <div class="page-title">{{ $account->name }}</div>
                <div class="page-sub">
                    {{ $account->phone }}
                    @if($account->email) &middot; {{ $account->email }} @endif
                    &middot; Joined {{ $account->created_at->format('d M Y') }}
                </div>
            </div>
            <div class="header-actions">
                <form method="POST" action="{{ route('admin.account.impersonate', $account) }}">
                    @csrf
                    <button type="submit" class="btn btn-blue">Login as this account</button>
                </form>
                <form method="POST" action="{{ route('admin.account.delete', $account) }}"
                      onsubmit="return confirm('Permanently delete {{ $account->name }} and ALL its data? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red">Delete account</button>
                </form>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-label">Total invoiced</div>
                <div class="kpi-value" style="color:#1a6b52">{{ currency($totalInvoiced) }}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Total collected</div>
                <div class="kpi-value" style="color:#1a6b52">{{ currency($totalPaid) }}</div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Outstanding</div>
                <div class="kpi-value" style="color:{{ ($totalInvoiced - $totalPaid) > 0 ? '#b91c1c' : '#1a6b52' }}">
                    {{ currency(max(0, $totalInvoiced - $totalPaid)) }}
                </div>
            </div>
            <div class="kpi">
                <div class="kpi-label">Units managed</div>
                <div class="kpi-value">{{ $unitCount }} / {{ $account->unit_limit }}</div>
            </div>
        </div>

        <div class="grid-2">
            {{-- Account info --}}
            <div class="card">
                <div class="card-title">Account details</div>
                <div class="field-row">
                    <span class="field-label">Plan</span>
                    <span class="field-value">{{ ucfirst($account->plan) }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Status</span>
                    <span>
                        @if($account->isOnTrial())
                            <span class="badge badge-amber">Trial &middot; {{ $account->trialDaysRemaining() }} days left</span>
                        @elseif($account->isActive())
                            <span class="badge badge-green">Active &middot; {{ $account->subscriptionDaysRemaining() }} days left</span>
                        @elseif($account->isInGracePeriod())
                            <span class="badge badge-amber">Grace &middot; {{ $account->graceDaysRemaining() }} days</span>
                        @else
                            <span class="badge badge-red">Expired</span>
                        @endif
                    </span>
                </div>
                <div class="field-row">
                    <span class="field-label">Unit limit</span>
                    <span class="field-value">{{ $account->unit_limit }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">SMS credits</span>
                    <span class="field-value">{{ number_format($account->sms_credits) }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Expires</span>
                    <span class="field-value">{{ $account->plan_expires_at?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Grace period ends</span>
                    <span class="field-value">{{ $account->grace_period_ends_at?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Currency</span>
                    <span class="field-value">{{ $account->currency ?? 'KES' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Use case</span>
                    <span class="field-value">{{ ucfirst(str_replace('_', ' ', $account->use_case ?? 'N/A')) }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Portfolio size</span>
                    <span class="field-value">{{ $account->unit_count_range ?? 'N/A' }} units</span>
                </div>

                {{-- Properties --}}
                @if($account->properties->isNotEmpty())
                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(0,0,0,.06)">
                        <div style="font-size:11px;font-weight:500;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">
                            Properties ({{ $account->properties->count() }})
                        </div>
                        @foreach($account->properties as $property)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:13px;border-bottom:1px solid rgba(0,0,0,.04)">
                                <div>
                                    <span style="font-weight:500">{{ $property->name }}</span>
                                    <span style="font-size:11px;color:#8a8880;margin-left:6px">{{ ucfirst($property->type) }}</span>
                                </div>
                                <span style="font-size:12px;color:#8a8880">
                                    {{ $property->units->where('status','occupied')->count() }}/{{ $property->units->count() }} occupied
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Users --}}
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(0,0,0,.06)">
                    <div style="font-size:11px;font-weight:500;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">
                        Users ({{ $account->users->count() }})
                    </div>
                    @foreach($account->users as $user)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:13px;border-bottom:1px solid rgba(0,0,0,.04)">
                            <div>
                                <span style="font-weight:500">{{ $user->name }}</span>
                                <span style="font-size:11px;color:#8a8880;margin-left:6px">{{ $user->email }}</span>
                            </div>
                            <span style="font-size:11px;color:#8a8880">{{ ucfirst($user->role) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:20px">

                {{-- Update subscription --}}
                <div class="card" style="margin-bottom:0">
                    <div class="card-title">Update subscription</div>
                    <form method="POST" action="{{ route('admin.account.update', $account) }}">
                        @csrf
                        <div class="form-group">
                            <label>Plan</label>
                            <select name="plan">
                                @foreach(['explore','starter','growth','pro','enterprise'] as $p)
                                    <option value="{{ $p }}" {{ $account->plan === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Plan expiry date</label>
                            <input type="date" name="plan_expires_at" value="{{ $account->plan_expires_at?->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Grace period ends</label>
                            <input type="date" name="grace_period_ends_at" value="{{ $account->grace_period_ends_at?->format('Y-m-d') }}">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="form-group">
                                <label>Unit limit</label>
                                <input type="number" name="unit_limit" value="{{ $account->unit_limit }}" min="1">
                            </div>
                            <div class="form-group">
                                <label>SMS credits / month</label>
                                <input type="number" name="sms_credits_monthly" value="{{ $account->sms_credits_monthly }}" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current SMS balance</label>
                            <input type="number" name="sms_credits" value="{{ $account->sms_credits }}" min="0">
                        </div>
                        <button type="submit" class="btn btn-green" style="width:100%;justify-content:center">Save changes</button>
                    </form>
                </div>

                {{-- Manual SMS top-up --}}
                <div class="card" style="margin-bottom:0">
                    <div class="card-title">Add SMS credits</div>
                    <form method="POST" action="{{ route('admin.account.top-up-sms', $account) }}">
                        @csrf
                        <div class="form-group">
                            <label>Credits to add</label>
                            <input type="number" name="credits" placeholder="e.g. 100" min="1" max="10000" required>
                        </div>
                        <div class="form-group">
                            <label>Note (optional)</label>
                            <input type="text" name="note" placeholder="e.g. Bonus credits for downtime">
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                            <span style="font-size:12px;color:#8a8880">Current balance: <strong>{{ number_format($account->sms_credits) }}</strong></span>
                        </div>
                        <button type="submit" class="btn btn-green" style="width:100%;justify-content:center">Add credits</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent payments --}}
        @if($recentPayments->count() > 0)
            <div class="card">
                <div class="card-title">Recent payments ({{ $recentPayments->count() }})</div>
                <table>
                    <thead><tr>
                        <th>Tenant</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Reference</th>
                    </tr></thead>
                    <tbody>
                        @foreach($recentPayments as $payment)
                            <tr>
                                <td style="font-weight:500">{{ $payment->tenant?->full_name ?? 'N/A' }}</td>
                                <td style="color:#1a6b52;font-weight:500">{{ currency($payment->amount) }}</td>
                                <td><span class="badge badge-gray">{{ strtoupper($payment->method) }}</span></td>
                                <td style="color:#8a8880">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td style="color:#8a8880;font-size:12px">{{ $payment->reference ?? '—' }}</td>
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