<x-layouts.app>
<style>
.set-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.set-layout {
    display: grid;
    grid-template-columns: 170px 1fr;
    gap: 22px;
    align-items: start;
}

.set-nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    position: sticky;
    top: 16px;
}

.sni {
    padding: 7px 11px;
    border-radius: 7px;
    font-size: 13px;
    cursor: pointer;
    color: #8a8880;
    border: none;
    background: transparent;
    text-align: left;
    font-family: 'DM Sans', sans-serif;
    width: 100%;
}

@media (max-width: 700px) {
    .set-layout { grid-template-columns: 1fr; gap: 14px; }
    .set-nav {
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        gap: 6px;
        padding-bottom: 4px;
        position: static;
    }
    .sni {
        white-space: nowrap;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 12px;
        width: auto;
    }
}

.form-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-3col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
.plans-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    max-width: 700px;
    margin-bottom: 16px;
}

@media (max-width: 700px) {
    .form-2col  { grid-template-columns: 1fr; }
    .form-3col  { grid-template-columns: 1fr 1fr; }
    .plans-grid { grid-template-columns: 1fr; max-width: 100%; }
}
@media (max-width: 400px) {
    .form-3col { grid-template-columns: 1fr; }
}

.modal-box {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 440px;
}
@media (max-width: 500px) {
    .modal-box { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
}

.cycle-toggle {
    display: inline-flex;
    background: #f5f4f0;
    border-radius: 8px;
    padding: 3px;
    margin-bottom: 14px;
}
.cycle-toggle button {
    padding: 6px 16px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    color: #8a8880;
    transition: all .15s;
}
.cycle-toggle button.active {
    background: #fff;
    color: #1a6b52;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.stk-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid rgba(26,107,82,0.15);
    border-top-color: #1a6b52;
    border-radius: 50%;
    animation: stkspin 0.8s linear infinite;
    margin: 0 auto 14px;
}
@keyframes stkspin { to { transform: rotate(360deg); } }

.section-divider {
    margin: 28px 0 16px;
    padding-top: 20px;
    border-top: 1px solid rgba(0,0,0,0.07);
}
</style>

@php
    $openPanel = old('_panel', session('_panel', 'account'));
@endphp

<div class="set-wrap">

    <div style="margin-bottom:24px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Settings</div>
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

    <div class="set-layout">

        {{-- Settings nav — 5 tabs --}}
        <div class="set-nav">
            @foreach([
                ['account',      'Account'],
                ['users',        'Users'],
                ['subscription', 'Subscription'],
                ['advanced',     'Advanced'],
            ] as [$id, $label])
                <button class="sni {{ $id === $openPanel ? 'on' : '' }}"
                        onclick="showPanel('{{ $id }}', this)"
                        id="nav-{{ $id }}"
                        style="{{ $id === $openPanel ? 'background:#e6f2ed;color:#1a6b52;font-weight:500' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Panels --}}
        <div>

            {{-- ── Account ── --}}
            <div id="panel-account" class="sp" style="display:{{ $openPanel === 'account' ? 'block' : 'none' }}">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Business details
                </div>
                <form method="POST" action="{{ route('settings.account') }}" enctype="multipart/form-data" style="max-width:480px">
                    @csrf
                    <div style="display:grid;gap:13px">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Business name</label>
                            <input name="name" type="text" required value="{{ $account->name }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Phone number</label>
                            <input name="phone" type="text" required value="{{ $account->phone }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Email address</label>
                            <input name="email" type="email" value="{{ $account->email }}"
                                   style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">County</label>
                            <select name="county" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                <option value="">Select county</option>
                                @foreach(['Nairobi','Kiambu','Mombasa','Kisumu','Nakuru','Machakos','Kajiado',"Murang'a",'Nyeri','Meru','Uasin Gishu','Kwale','Kilifi','Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kakamega','Kericho','Kirinyaga','Kisii','Kitui','Laikipia','Lamu','Makueni','Mandera','Marsabit','Migori','Nandi','Narok','Nyamira','Nyandarua','Samburu','Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia','Turkana','Vihiga','Wajir','West Pokot'] as $county)
                                    <option value="{{ $county }}" {{ $account->county == $county ? 'selected' : '' }}>{{ $county }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Currency</label>
                            <select name="currency" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                @foreach(\App\Helpers\CurrencyHelper::all() as $code => $info)
                                    <option value="{{ $code }}" {{ ($account->currency ?? 'KES') === $code ? 'selected' : '' }}>
                                        {{ $info['flag'] }} {{ $info['name'] }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Company logo</label>
                            @if($account->logo_path)
                                <div style="margin-bottom:8px;display:flex;align-items:center;gap:10px">
                                    <img src="{{ asset('storage/'.$account->logo_path) }}" alt="Logo"
                                         style="height:48px;object-fit:contain;border-radius:6px;border:1px solid rgba(0,0,0,0.07);padding:6px;background:#fff">
                                    <span style="font-size:12px;color:#8a8880">Current logo</span>
                                </div>
                            @endif
                            <input name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                                   style="width:100%;font-size:13px;font-family:'DM Sans',sans-serif;color:#111110">
                            <div style="font-size:11px;color:#8a8880;margin-top:3px">PNG/JPG/WebP, max 2MB</div>
                        </div>
                    </div>
                    <div style="margin-top:16px">
                        <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Users ── --}}
            <div id="panel-users" class="sp" style="display:{{ $openPanel === 'users' ? 'block' : 'none' }}">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07);flex-wrap:wrap;gap:8px">
                    <div style="font-size:15px;font-weight:500">Users and Roles</div>
                    <button onclick="document.getElementById('invite-modal').style.display='flex';toggleInviteProperties()"
                            style="padding:6px 14px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        + Add user
                    </button>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#8a8880">
                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#e6f2ed;color:#1a6b52">Owner</span>
                        Full access
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#8a8880">
                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#dbeafe;color:#1e40af">Manager</span>
                        No settings
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#8a8880">
                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Caretaker</span>
                        Properties &amp; maintenance only
                    </div>
                </div>

                <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:10px;border:1px solid rgba(0,0,0,0.07)">
                    <table style="width:100%;border-collapse:collapse;min-width:400px">
                        <thead>
                            <tr>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Name</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Email</th>
                                <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Role</th>
                                <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr style="border-bottom:1px solid rgba(0,0,0,0.05);background:#fff">
                                    <td style="padding:11px 14px">
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <div style="width:26px;height:26px;border-radius:50%;background:#e6f2ed;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                                {{ strtoupper(substr($user->name,0,2)) }}
                                            </div>
                                            <span style="font-size:13px;font-weight:500">{{ $user->name }}</span>
                                            @if($user->id===auth()->id())
                                                <span style="font-size:11px;color:#8a8880">(you)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $user->email }}</td>
                                    <td style="padding:11px 14px">
                                        @php
                                            $roleBadge = match($user->role) {
                                                'owner'     => ['#e6f2ed', '#1a6b52', 'Owner'],
                                                'manager'   => ['#dbeafe', '#1e40af', 'Manager'],
                                                'caretaker' => ['#fef3c7', '#92400e', 'Caretaker'],
                                                default     => ['#f3f4f6', '#4b5563', ucfirst($user->role)],
                                            };
                                        @endphp
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $roleBadge[0] }};color:{{ $roleBadge[1] }}">
                                            {{ $roleBadge[2] }}
                                        </span>
                                        @if(!$user->isOwner())
                                            <div style="font-size:11px;color:#8a8880;margin-top:3px">
                                                {{ $user->assignedProperties->count() }} {{ Str::plural('property', $user->assignedProperties->count()) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:11px 14px;text-align:right;white-space:nowrap">
                                        @if(!$user->isOwner())
                                            <button type="button"
                                                    onclick="openPropertiesModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->assignedProperties->pluck('id')->toJson() }})"
                                                    style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#1a6b52;border:1px solid rgba(26,107,82,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;margin-right:6px">
                                                Manage properties
                                            </button>
                                        @endif
                                        @if($user->id!==auth()->id())
                                            <form method="POST" action="{{ route('settings.users.remove',$user) }}"
                                                  onsubmit="return confirm('Remove {{ $user->name }}?')" style="display:inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" style="display:inline-flex;align-items:center;padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Subscription ── --}}
            <div id="panel-subscription" class="sp" style="display:{{ $openPanel === 'subscription' ? 'block' : 'none' }}">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Subscription and Billing
                </div>

                @if(session('mpesa_message'))
                    <div style="background:#e6f2ed;border:1px solid #a7d7c5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
                        {{ session('mpesa_message') }}
                    </div>
                @endif

                <div style="background:#fff;border-radius:10px;border:1px solid {{ $account->isExpired()?'#fca5a5':'#1a6b52' }};border-left-width:3px;padding:20px;max-width:560px;margin-bottom:20px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.07);flex-wrap:wrap;gap:8px">
                        <div>
                            <div style="font-size:15px;font-weight:500">{{ $account->planName() }} plan</div>
                            <div style="font-size:12px;color:#8a8880;margin-top:2px">{{ $account->unit_limit }} units &middot; {{ $account->sms_credits_monthly }} SMS/month</div>
                        </div>
                        @if($account->isOnTrial())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Trial &middot; {{ $account->trialDaysRemaining() }}d</span>
                        @elseif($account->isInGracePeriod())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fee2e2;color:#991b1b">Grace &middot; {{ $account->graceDaysRemaining() }}d</span>
                        @elseif($account->isActive())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#e6f2ed;color:#1a6b52">Active &middot; {{ $account->subscriptionDaysRemaining() }}d</span>
                        @else
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fee2e2;color:#991b1b">Expired</span>
                        @endif
                    </div>
                    <div style="font-size:13px;display:grid;gap:7px">
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:#8a8880">Units used</span>
                            <span style="font-weight:500">{{ $account->currentUnitCount() }} of {{ $account->unit_limit }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:#8a8880">SMS credits</span>
                            <span style="font-weight:500;color:{{ $account->sms_credits<=20?'#b91c1c':'#111110' }}">{{ number_format($account->sms_credits) }}</span>
                        </div>
                        @if($account->plan_expires_at)
                            <div style="display:flex;justify-content:space-between">
                                <span style="color:#8a8880">{{ $account->isActive()?'Expires':'Expired' }}</span>
                                <span>{{ $account->plan_expires_at->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px">
                    <div style="font-size:13px;font-weight:500">Available plans</div>
                    <div class="cycle-toggle">
                        <button type="button" class="active" id="cycle-monthly" onclick="setCycle('monthly')">Monthly</button>
                        <button type="button" id="cycle-yearly" onclick="setCycle('yearly')">Yearly</button>
                    </div>
                </div>

                <div class="plans-grid">
                    @foreach(['starter','growth','pro'] as $planKey)
                        @php
                            $plan      = \App\Models\Account::PLANS[$planKey];
                            $isCurrent = $account->plan === $planKey;
                        @endphp
                        <div style="background:#fff;border-radius:10px;border:2px solid {{ $isCurrent?'#1a6b52':'rgba(0,0,0,0.07)' }};padding:16px;position:relative">
                            @if($isCurrent)
                                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#1a6b52;color:#fff;font-size:10px;font-weight:600;padding:2px 10px;border-radius:10px;white-space:nowrap">CURRENT</div>
                            @endif
                            <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:6px">{{ $plan['name'] }}</div>
                            <div class="price-monthly">
                                <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:2px">{{ currency($plan['price_monthly']) }}</div>
                                <div style="font-size:11px;color:#8a8880;margin-bottom:6px">per month</div>
                            </div>
                            <div class="price-yearly" style="display:none">
                                <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:2px">{{ currency($plan['price_yearly']) }}</div>
                                <div style="font-size:11px;color:#8a8880;margin-bottom:6px">per year</div>
                            </div>
                            <div style="border-top:1px solid rgba(0,0,0,0.06);padding-top:10px;font-size:12px;display:grid;gap:3px;color:#8a8880;margin-bottom:12px">
                                <div>Up to {{ $plan['unit_limit'] }} units</div>
                                <div>{{ $plan['sms_credits_monthly'] }} SMS/month</div>
                            </div>
                            <button type="button"
                                    onclick="openUpgradeModal('{{ $planKey }}', '{{ $plan['name'] }}')"
                                    style="width:100%;padding:7px;background:{{ $isCurrent?'transparent':'#1a6b52' }};color:{{ $isCurrent?'#1a6b52':'#fff' }};border:1px solid #1a6b52;border-radius:7px;font-size:12px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                                {{ $isCurrent ? 'Renew / Extend' : 'Upgrade' }}
                            </button>
                        </div>
                    @endforeach
                </div>

                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 18px;max-width:700px;font-size:13px">
                    <div style="font-weight:500;margin-bottom:6px">Pay with M-Pesa</div>
                    <div style="color:#8a8880;line-height:1.7">
                        Click "Upgrade" to pay via M-Pesa STK push — your account upgrades automatically once payment is confirmed.
                    </div>
                </div>
            </div>

            {{-- ── Advanced (Password + Danger zone) ── --}}
            <div id="panel-advanced" class="sp" style="display:{{ $openPanel === 'advanced' ? 'block' : 'none' }}">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">Change password</div>
                <form method="POST" action="{{ route('settings.password') }}" style="max-width:400px">
                    @csrf
                    <div style="display:grid;gap:13px">
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Current password</label>
                            <input name="current_password" type="password" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            @error('current_password')<div style="font-size:12px;color:#b91c1c;margin-top:3px">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">New password</label>
                            <input name="password" type="password" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                        <div>
                            <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Confirm new password</label>
                            <input name="password_confirmation" type="password" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        </div>
                    </div>
                    <div style="margin-top:16px">
                        <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Update password
                        </button>
                    </div>
                </form>

                <div class="section-divider">
                    <div style="font-size:15px;font-weight:500;color:#991b1b;margin-bottom:6px">Danger zone</div>
                </div>

                <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:20px;max-width:560px">
                    <div style="display:flex;align-items:flex-start;gap:14px">
                        <div style="font-size:22px;flex-shrink:0;margin-top:2px">⚠️</div>
                        <div>
                            <div style="font-size:14px;font-weight:600;color:#991b1b;margin-bottom:6px">Reset account data</div>
                            <div style="font-size:13px;color:#7f1d1d;line-height:1.6;margin-bottom:16px">
                                Permanently deletes all properties, tenants, invoices, payments and related data.
                                Your account and users are kept. This cannot be undone.
                            </div>
                            @if(auth()->user()->role === 'owner')
                                <button type="button"
                                        onclick="document.getElementById('reset-modal').style.display='flex'"
                                        style="padding:8px 18px;background:#b91c1c;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                                    Reset account data
                                </button>
                            @else
                                <div style="font-size:13px;color:#991b1b;font-style:italic">
                                    Only the account owner can perform a reset.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Invite User Modal --}}
<div id="invite-modal"
     style="display:{{ $errors->any() && $openPanel === 'users' ? 'flex' : 'none' }};position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Add a user</div>
            <button onclick="document.getElementById('invite-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.users.invite') }}">
            @csrf
            <input type="hidden" name="_panel" value="users">
            @if($errors->any())
                <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:7px;padding:10px 12px;margin-bottom:14px;font-size:12px;color:#991b1b">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif
            <div style="display:grid;gap:13px;margin-bottom:18px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Full name</label>
                    <input name="name" type="text" required value="{{ old('name') }}" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @error('name')<div style="font-size:11px;color:#b91c1c;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Email address</label>
                    <input name="email" type="email" required value="{{ old('email') }}" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @error('email')<div style="font-size:11px;color:#b91c1c;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Phone number</label>
                    <input name="phone" type="text" required placeholder="07XX" value="{{ old('phone') }}" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    @error('phone')<div style="font-size:11px;color:#b91c1c;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Role</label>
                    <select name="role" id="invite-role" required onchange="toggleInviteProperties()" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="owner" {{ old('role')==='owner'?'selected':'' }}>Owner — full access</option>
                        <option value="manager" {{ old('role')==='manager'?'selected':'' }}>Manager — no settings</option>
                        <option value="caretaker" {{ old('role')==='caretaker'?'selected':'' }}>Caretaker — properties only</option>
                    </select>
                </div>
            </div>

            <div id="invite-properties" style="display:{{ in_array(old('role'), ['manager','caretaker']) ? 'block' : 'none' }};margin-bottom:16px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px">Assign to properties</label>
                @if($properties->isEmpty())
                    <div style="font-size:12px;color:#8a8880">Add a property first to assign one.</div>
                @else
                    <div style="display:grid;gap:6px;max-height:160px;overflow-y:auto;border:1px solid rgba(0,0,0,0.08);border-radius:7px;padding:10px">
                        @foreach($properties as $property)
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                                <input type="checkbox" name="property_ids[]" value="{{ $property->id }}"
                                       {{ in_array($property->id, old('property_ids', [])) ? 'checked' : '' }}
                                       style="width:15px;height:15px;accent-color:#1a6b52">
                                {{ $property->name }}
                            </label>
                        @endforeach
                    </div>
                @endif
                <div style="font-size:11px;color:#8a8880;margin-top:6px">They'll only see the properties checked here. Leave unchecked and add access later from the Users list.</div>
            </div>
            <div style="background:#f5f4f0;border-radius:7px;padding:10px 12px;font-size:12px;color:#8a8880;margin-bottom:16px">
                Temporary password: <strong>password123</strong>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Add user
                </button>
                <button type="button" onclick="document.getElementById('invite-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Manage Properties Modal --}}
<div id="properties-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Properties for <span id="properties-modal-name"></span></div>
            <button onclick="document.getElementById('properties-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" id="properties-modal-form">
            @csrf
            @if($properties->isEmpty())
                <div style="font-size:13px;color:#8a8880;margin-bottom:16px">No properties yet — add one first.</div>
            @else
                <div style="display:grid;gap:6px;max-height:280px;overflow-y:auto;border:1px solid rgba(0,0,0,0.08);border-radius:7px;padding:10px;margin-bottom:16px">
                    @foreach($properties as $property)
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                            <input type="checkbox" name="property_ids[]" value="{{ $property->id }}" class="properties-modal-checkbox"
                                   style="width:15px;height:15px;accent-color:#1a6b52">
                            {{ $property->name }}
                        </label>
                    @endforeach
                </div>
            @endif
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save access
                </button>
                <button type="button" onclick="document.getElementById('properties-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- M-Pesa Upgrade Modal --}}
<div id="upgrade-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500" id="upgrade-title">Upgrade plan</div>
            <button onclick="closeUpgradeModal()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <div id="upgrade-step-form">
            <div style="background:#f5f4f0;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;display:flex;justify-content:space-between">
                <span style="color:#8a8880">Amount to pay</span>
                <span style="font-weight:500;font-family:'DM Serif Display',serif;font-size:16px" id="upgrade-amount">KES 0</span>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">M-Pesa phone number</label>
                <input type="text" id="upgrade-phone" placeholder="07XXXXXXXX"
                       style="width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none">
                <div id="upgrade-error" style="display:none;color:#b91c1c;font-size:12px;margin-top:6px"></div>
            </div>
            <button type="button" id="upgrade-pay-btn" onclick="initiateStkPush()"
                    style="width:100%;padding:10px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Pay with M-Pesa
            </button>
        </div>
        <div id="upgrade-step-waiting" style="display:none;text-align:center;padding:20px 0">
            <div class="stk-spinner"></div>
            <div style="font-size:14px;font-weight:500;margin-bottom:6px">Check your phone</div>
            <div style="font-size:13px;color:#8a8880;line-height:1.6">
                Enter your M-Pesa PIN sent to <strong id="upgrade-phone-display"></strong>.
            </div>
            <div style="font-size:12px;color:#8a8880;margin-top:14px" id="upgrade-waiting-status">Waiting for confirmation...</div>
        </div>
        <div id="upgrade-step-success" style="display:none;text-align:center;padding:20px 0">
            <div style="font-size:36px;margin-bottom:10px">✅</div>
            <div style="font-size:14px;font-weight:500;margin-bottom:6px">Payment successful</div>
            <button type="button" onclick="window.location.reload()"
                    style="padding:8px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Continue
            </button>
        </div>
        <div id="upgrade-step-failed" style="display:none;text-align:center;padding:20px 0">
            <div style="font-size:36px;margin-bottom:10px">⚠️</div>
            <div style="font-size:14px;font-weight:500;margin-bottom:6px">Payment not completed</div>
            <div style="font-size:13px;color:#8a8880;line-height:1.6;margin-bottom:18px" id="upgrade-failed-desc">The payment was cancelled or did not go through.</div>
            <button type="button" onclick="resetUpgradeModal()"
                    style="padding:8px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Try again
            </button>
        </div>
    </div>
</div>

{{-- Reset Account Modal --}}
<div id="reset-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:460px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:600;color:#991b1b">Reset account data</div>
            <button onclick="closeResetModal()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px;margin-bottom:20px;font-size:13px;color:#7f1d1d;line-height:1.6">
            This cannot be undone.
        </div>
        <form method="POST" action="{{ route('settings.reset-account') }}" id="reset-form">
            @csrf
            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:500;color:#991b1b;margin-bottom:8px">
                    Type <strong>RESET</strong> to confirm:
                </label>
                <input type="text" name="confirmation" id="reset-confirmation" placeholder="RESET" autocomplete="off"
                       oninput="checkResetInput(this)"
                       style="width:100%;height:40px;padding:0 14px;border:2px solid #fca5a5;border-radius:7px;font-size:15px;font-family:'DM Sans',sans-serif;outline:none;letter-spacing:.08em;text-transform:uppercase">
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" id="reset-submit-btn" disabled
                        style="padding:8px 20px;background:#e5e7eb;color:#9ca3af;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:not-allowed;font-family:'DM Sans',sans-serif;transition:all .15s"
                        onclick="return confirm('Last chance — this will permanently delete all data. Are you absolutely sure?')">
                    Yes, reset everything
                </button>
                <button type="button" onclick="closeResetModal()"
                        style="padding:8px 15px;background:transparent;color:#6b7280;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInviteProperties() {
    var role = document.getElementById('invite-role').value;
    document.getElementById('invite-properties').style.display = (role === 'manager' || role === 'caretaker') ? 'block' : 'none';
}

function openPropertiesModal(userId, userName, assignedIds) {
    document.getElementById('properties-modal-name').textContent = userName;
    document.getElementById('properties-modal-form').action = '/settings/users/' + userId + '/properties';
    document.querySelectorAll('.properties-modal-checkbox').forEach(function (cb) {
        cb.checked = assignedIds.includes(parseInt(cb.value, 10));
    });
    document.getElementById('properties-modal').style.display = 'flex';
}

function showPanel(id, el) {
    document.querySelectorAll('.sp').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + id).style.display = 'block';
    document.querySelectorAll('.sni').forEach(n => {
        n.style.background = 'transparent';
        n.style.color      = '#8a8880';
        n.style.fontWeight = '400';
        n.style.border     = '1px solid rgba(0,0,0,0.1)';
    });
    el.style.background = '#e6f2ed';
    el.style.color      = '#1a6b52';
    el.style.fontWeight = '500';
    el.style.border     = '1px solid #a7d7c5';
}

function fixNavBorders() {
    if (window.innerWidth > 700) {
        document.querySelectorAll('.sni').forEach(n => n.style.border = 'none');
    }
}
window.addEventListener('resize', fixNavBorders);
fixNavBorders();

var currentCycle = 'monthly';
var planPrices = {
    starter: { monthly: {{ \App\Models\Account::PLANS['starter']['price_monthly'] }}, yearly: {{ \App\Models\Account::PLANS['starter']['price_yearly'] }} },
    growth:  { monthly: {{ \App\Models\Account::PLANS['growth']['price_monthly'] }},  yearly: {{ \App\Models\Account::PLANS['growth']['price_yearly'] }} },
    pro:     { monthly: {{ \App\Models\Account::PLANS['pro']['price_monthly'] }},     yearly: {{ \App\Models\Account::PLANS['pro']['price_yearly'] }} },
};

function setCycle(cycle) {
    currentCycle = cycle;
    document.getElementById('cycle-monthly').classList.toggle('active', cycle === 'monthly');
    document.getElementById('cycle-yearly').classList.toggle('active', cycle === 'yearly');
    document.querySelectorAll('.price-monthly').forEach(el => el.style.display = cycle === 'monthly' ? 'block' : 'none');
    document.querySelectorAll('.price-yearly').forEach(el => el.style.display = cycle === 'yearly' ? 'block' : 'none');
}

var selectedPlan = null;
var pollTimer    = null;

function openUpgradeModal(planKey, planName) {
    selectedPlan = planKey;
    document.getElementById('upgrade-title').textContent = 'Upgrade to ' + planName;
    document.getElementById('upgrade-amount').textContent =
        '{{ currency_symbol() }} ' + planPrices[planKey][currentCycle].toLocaleString() + ' / ' + (currentCycle === 'monthly' ? 'month' : 'year');
    resetUpgradeModal();
    document.getElementById('upgrade-modal').style.display = 'flex';
}

function closeUpgradeModal() {
    document.getElementById('upgrade-modal').style.display = 'none';
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function resetUpgradeModal() {
    document.getElementById('upgrade-step-form').style.display    = 'block';
    document.getElementById('upgrade-step-waiting').style.display = 'none';
    document.getElementById('upgrade-step-success').style.display = 'none';
    document.getElementById('upgrade-step-failed').style.display  = 'none';
    document.getElementById('upgrade-error').style.display        = 'none';
    document.getElementById('upgrade-pay-btn').disabled           = false;
    document.getElementById('upgrade-pay-btn').textContent        = 'Pay with M-Pesa';
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function initiateStkPush() {
    var phone = document.getElementById('upgrade-phone').value.trim();
    var errEl = document.getElementById('upgrade-error');
    if (!/^(0[71][0-9]{8}|254[71][0-9]{8}|\+254[71][0-9]{8})$/.test(phone)) {
        errEl.textContent = 'Enter a valid M-Pesa number, e.g. 0712345678';
        errEl.style.display = 'block';
        return;
    }
    errEl.style.display = 'none';
    var btn = document.getElementById('upgrade-pay-btn');
    btn.disabled = true;
    btn.textContent = 'Sending request...';
    fetch('{{ route('subscription.upgrade') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ plan: selectedPlan, billing_cycle: currentCycle, phone: phone }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            errEl.textContent = data.error || data.message || 'Could not start payment. Please try again.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Pay with M-Pesa';
            return;
        }
        document.getElementById('upgrade-step-form').style.display    = 'none';
        document.getElementById('upgrade-step-waiting').style.display = 'block';
        document.getElementById('upgrade-phone-display').textContent   = phone;
        pollStkStatus(data.checkout_request_id);
    })
    .catch(() => {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Pay with M-Pesa';
    });
}

function pollStkStatus(checkoutRequestId) {
    var attempts = 0;
    var statusEl = document.getElementById('upgrade-waiting-status');
    pollTimer = setInterval(function () {
        attempts++;
        if (attempts > 40) {
            clearInterval(pollTimer);
            showFailed('Payment timed out. If you completed it on your phone, refresh the page in a moment.');
            return;
        }
        fetch('{{ url('/subscription/status') }}/' + checkoutRequestId)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    clearInterval(pollTimer);
                    document.getElementById('upgrade-step-waiting').style.display = 'none';
                    document.getElementById('upgrade-step-success').style.display = 'block';
                } else if (data.status === 'failed' || data.status === 'cancelled') {
                    clearInterval(pollTimer);
                    showFailed(data.desc || 'The payment was cancelled or did not go through.');
                } else {
                    statusEl.textContent = 'Waiting for confirmation' + '.'.repeat((attempts % 3) + 1);
                }
            })
            .catch(() => {});
    }, 3000);
}

function showFailed(message) {
    document.getElementById('upgrade-step-waiting').style.display = 'none';
    document.getElementById('upgrade-step-failed').style.display  = 'block';
    document.getElementById('upgrade-failed-desc').textContent    = message;
}

function closeResetModal() {
    document.getElementById('reset-modal').style.display = 'none';
    document.getElementById('reset-confirmation').value  = '';
    var btn = document.getElementById('reset-submit-btn');
    btn.disabled         = true;
    btn.style.background = '#e5e7eb';
    btn.style.color      = '#9ca3af';
    btn.style.cursor     = 'not-allowed';
}

function checkResetInput(input) {
    var btn   = document.getElementById('reset-submit-btn');
    var valid = input.value.trim().toUpperCase() === 'RESET';
    btn.disabled         = !valid;
    btn.style.background = valid ? '#b91c1c' : '#e5e7eb';
    btn.style.color      = valid ? '#fff'    : '#9ca3af';
    btn.style.cursor     = valid ? 'pointer' : 'not-allowed';
}
</script>

</x-layouts.app>