<x-layouts.app>
<style>
.set-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

/* Two-column layout: nav + panel */
.set-layout {
    display: grid;
    grid-template-columns: 190px 1fr;
    gap: 22px;
    align-items: start;
}

/* Nav pills */
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

/* Mobile: nav becomes horizontal scrollable pill row */
@media (max-width: 700px) {
    .set-layout {
        grid-template-columns: 1fr;
        gap: 14px;
    }
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

/* Form grids */
.form-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.form-3col {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
}
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
    .form-3col  { grid-template-columns: 1fr; }
}

/* Modal */
.modal-box {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 440px;
}
@media (max-width: 500px) {
    .modal-box {
        width: calc(100vw - 24px);
        padding: 20px;
        border-radius: 12px;
    }
}
</style>

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

        {{-- Settings nav --}}
        <div class="set-nav">
            @foreach([
                ['account',      'Account'],
                ['schedule',     'Invoices'],
                ['reports',      'Reports'],
                ['password',     'Password'],
                ['users',        'Users'],

                ['subscription', 'Subscription'],
                ['danger',       'Danger zone'],
            ] as [$id, $label])
                <button class="sni {{ $id === 'account' ? 'on' : '' }}"
                        onclick="showPanel('{{ $id }}', this)"
                        id="nav-{{ $id }}"
                        style="{{ $id === 'account' ? 'background:#e6f2ed;color:#1a6b52;font-weight:500' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Panels --}}
        <div>

            {{-- ── Account ── --}}
            <div id="panel-account" class="sp" style="display:block">
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
                            <div style="font-size:11px;color:#8a8880;margin-top:3px">All amounts in the app and on invoices will use this currency</div>
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
                            <div style="font-size:11px;color:#8a8880;margin-top:3px">PNG, JPG or WebP. Max 2MB. Appears on PDF invoices.</div>
                        </div>
                    </div>
                    <div style="margin-top:16px">
                        <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>


            {{-- ── Invoice schedule ── --}}
            <div id="panel-schedule" class="sp" style="display:none">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Automatic invoice schedule
                </div>
                <form method="POST" action="{{ route('settings.invoice-schedule') }}" style="max-width:480px">
                    @csrf
                    <div style="margin-bottom:16px">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;padding:14px 16px;background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:10px">
                            <input type="checkbox" name="auto_invoice_enabled" value="1"
                                   {{ $account->auto_invoice_enabled?'checked':'' }}
                                   style="width:16px;height:16px;accent-color:#1a6b52;flex-shrink:0">
                            <div>
                                <div style="font-weight:500">Enable automatic invoice generation</div>
                                <div style="font-size:12px;color:#8a8880;margin-top:2px">System will automatically generate and send invoices on the configured day each month</div>
                            </div>
                        </label>
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Send invoices on day</label>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <select name="invoice_send_day" required style="width:120px;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                @foreach(range(1,28) as $day)
                                    <option value="{{ $day }}" {{ $account->invoice_send_day==$day?'selected':'' }}>
                                        {{ $day }}{{ $day==1?'st':($day==2?'nd':($day==3?'rd':'th')) }}
                                    </option>
                                @endforeach
                            </select>
                            <span style="font-size:13px;color:#8a8880">of every month</span>
                        </div>
                        <div style="font-size:11px;color:#8a8880;margin-top:5px">Choose between 1st and 28th to avoid month-end issues</div>
                    </div>
                    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#92400e">
                        Make sure all meter readings are entered before this date each month.
                    </div>
                    <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Save schedule
                    </button>
                </form>
            </div>

            {{-- ── Report alerts ── --}}
            <div id="panel-reports" class="sp" style="display:none">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Report alerts
                </div>
                <div style="font-size:13px;color:#8a8880;margin-bottom:20px">
                    Receive automated SMS reports. Sent to: <strong style="color:#111110">{{ $account->phone }}</strong>
                </div>
                <form method="POST" action="{{ route('settings.report-alerts') }}">
                    @csrf
                    @php
                        $days   = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                        $times  = [];
                        for($h=0;$h<24;$h++) for($m=0;$m<60;$m+=30) $times[]=str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($m,2,'0',STR_PAD_LEFT);
                    @endphp

                    @foreach([
                        ['weekly',  'Weekly report',  'Collections, payments and maintenance summary for the week'],
                        ['monthly', 'Monthly report', 'Income, expenses, occupancy and top balances for the month'],
                    ] as [$freq, $title, $desc])
                        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px;margin-bottom:12px">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                                <div>
                                    <div style="font-size:13px;font-weight:500">{{ $title }}</div>
                                    <div style="font-size:12px;color:#8a8880;margin-top:2px">{{ $desc }}</div>
                                </div>
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex-shrink:0">
                                    <input type="checkbox" name="{{ $freq }}_report_enabled" value="1"
                                           {{ $account->{$freq.'_report_enabled'}?'checked':'' }}
                                           style="width:16px;height:16px;accent-color:#1a6b52">
                                    <span style="font-size:12px;color:#8a8880">Enable</span>
                                </label>
                            </div>
                            <div class="form-2col">
                                <div>
                                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">
                                        {{ $freq==='weekly'?'Day of week':'Day of month' }}
                                    </label>
                                    <select name="{{ $freq }}_report_day"
                                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                        @if($freq==='weekly')
                                            @foreach($days as $i=>$day)
                                                <option value="{{ $i }}" {{ ($account->{$freq.'_report_day'}??0)==$i?'selected':'' }}>{{ $day }}</option>
                                            @endforeach
                                        @else
                                            @foreach(range(1,28) as $day)
                                                <option value="{{ $day }}" {{ ($account->{$freq.'_report_day'}??1)==$day?'selected':'' }}>
                                                    {{ $day }}{{ $day==1?'st':($day==2?'nd':($day==3?'rd':'th')) }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Time</label>
                                    <select name="{{ $freq }}_report_time"
                                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                        @foreach($times as $time)
                                            <option value="{{ $time }}" {{ ($account->{$freq.'_report_time'}??'08:00')===$time?'selected':'' }}>
                                                {{ \Carbon\Carbon::createFromFormat('H:i',$time)->format('g:i A') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Yearly --}}
                    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px;margin-bottom:20px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                            <div>
                                <div style="font-size:13px;font-weight:500">Yearly report</div>
                                <div style="font-size:12px;color:#8a8880;margin-top:2px">Annual income, expenses, net profit and best performing month</div>
                            </div>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex-shrink:0">
                                <input type="checkbox" name="yearly_report_enabled" value="1"
                                       {{ $account->yearly_report_enabled?'checked':'' }}
                                       style="width:16px;height:16px;accent-color:#1a6b52">
                                <span style="font-size:12px;color:#8a8880">Enable</span>
                            </label>
                        </div>
                        <div class="form-3col">
                            <div>
                                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Month</label>
                                <select name="yearly_report_month" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                    @foreach($months as $i=>$monthName)
                                        <option value="{{ $i+1 }}" {{ ($account->yearly_report_month??1)==($i+1)?'selected':'' }}>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Day</label>
                                <select name="yearly_report_day" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                    @foreach(range(1,28) as $day)
                                        <option value="{{ $day }}" {{ ($account->yearly_report_day??1)==$day?'selected':'' }}>
                                            {{ $day }}{{ $day==1?'st':($day==2?'nd':($day==3?'rd':'th')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Time</label>
                                <select name="yearly_report_time" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                                    @foreach($times as $time)
                                        <option value="{{ $time }}" {{ ($account->yearly_report_time??'08:00')===$time?'selected':'' }}>
                                            {{ \Carbon\Carbon::createFromFormat('H:i',$time)->format('g:i A') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Save report settings
                    </button>
                </form>
            </div>

            {{-- ── Password ── --}}
            <div id="panel-password" class="sp" style="display:none">
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
            </div>

            {{-- ── Users ── --}}
            <div id="panel-users" class="sp" style="display:none">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07);flex-wrap:wrap;gap:8px">
                    <div style="font-size:15px;font-weight:500">Users and Roles</div>
                    <button onclick="document.getElementById('invite-modal').style.display='flex'"
                            style="padding:6px 14px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        + Add user
                    </button>
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
                                        <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $user->role==='owner'?'#e6f2ed':'#f3f4f6' }};color:{{ $user->role==='owner'?'#1a6b52':'#4b5563' }}">
                                            {{ $user->role==='owner'?'Owner':'Read only' }}
                                        </span>
                                    </td>
                                    <td style="padding:11px 14px;text-align:right">
                                        @if($user->id!==auth()->id())
                                            <form method="POST" action="{{ route('settings.users.remove',$user) }}"
                                                  onsubmit="return confirm('Remove {{ $user->name }}?')">
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
            <div id="panel-subscription" class="sp" style="display:none">
                <div style="font-size:15px;font-weight:500;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Subscription and Billing
                </div>

                {{-- Current plan --}}
                <div style="background:#fff;border-radius:10px;border:1px solid {{ $account->isExpired()?'#fca5a5':'#1a6b52' }};border-left-width:3px;padding:20px;max-width:560px;margin-bottom:20px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.07);flex-wrap:wrap;gap:8px">
                        <div>
                            <div style="font-size:15px;font-weight:500">{{ $account->planName() }} plan</div>
                            <div style="font-size:12px;color:#8a8880;margin-top:2px">{{ $account->unit_limit }} unit limit &middot; {{ $account->sms_credits_monthly }} SMS/month</div>
                        </div>
                        @if($account->isOnTrial())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fef3c7;color:#92400e">Trial &middot; {{ $account->trialDaysRemaining() }} days left</span>
                        @elseif($account->isInGracePeriod())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#fee2e2;color:#991b1b">Grace &middot; {{ $account->graceDaysRemaining() }} days</span>
                        @elseif($account->isActive())
                            <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:#e6f2ed;color:#1a6b52">Active &middot; {{ $account->subscriptionDaysRemaining() }} days left</span>
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
                            <span style="color:#8a8880">SMS credits remaining</span>
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

                {{-- Plans --}}
                <div style="font-size:13px;font-weight:500;margin-bottom:12px">Available plans</div>
                <div class="plans-grid">
                    @foreach([
                        'starter'=>['monthly'=>'KES 2,000','yearly'=>'KES 14,000','saving'=>'Save 2 months'],
                        'growth' =>['monthly'=>'KES 4,500','yearly'=>'KES 31,500','saving'=>'Save 2 months'],
                        'pro'    =>['monthly'=>'KES 7,000','yearly'=>'KES 49,000','saving'=>'Save 2 months'],
                    ] as $planKey=>$prices)
                        @php $plan = \App\Models\Account::PLANS[$planKey]; @endphp
                        <div style="background:#fff;border-radius:10px;border:2px solid {{ $account->plan===$planKey?'#1a6b52':'rgba(0,0,0,0.07)' }};padding:16px;position:relative">
                            @if($account->plan===$planKey)
                                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#1a6b52;color:#fff;font-size:10px;font-weight:600;padding:2px 10px;border-radius:10px;white-space:nowrap">CURRENT</div>
                            @endif
                            <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#8a8880;margin-bottom:6px">{{ $plan['name'] }}</div>
                            <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:2px">{{ $prices['monthly'] }}</div>
                            <div style="font-size:11px;color:#8a8880;margin-bottom:6px">per month</div>
                            <div style="font-size:11px;color:#1a6b52;font-weight:500;margin-bottom:10px">{{ $prices['yearly'] }}/yr &middot; {{ $prices['saving'] }}</div>
                            <div style="border-top:1px solid rgba(0,0,0,0.06);padding-top:10px;font-size:12px;display:grid;gap:3px;color:#8a8880">
                                <div>Up to {{ $plan['unit_limit'] }} units</div>
                                <div>{{ $plan['sms_credits_monthly'] }} SMS/month</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="background:#111110;border-radius:10px;padding:16px 20px;max-width:700px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#fff">Enterprise</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-top:2px">100+ units &middot; Custom SMS &middot; Dedicated support</div>
                    </div>
                    <a href="mailto:{{ config('app.support_email','support@nyumba.co.ke') }}"
                       style="padding:7px 14px;background:#1a6b52;color:#fff;border-radius:7px;font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap">
                        Contact us
                    </a>
                </div>

                <div style="background:#e6f2ed;border:1px solid #a7d7c5;border-radius:10px;padding:14px 16px;max-width:700px;margin-bottom:16px;font-size:13px">
                    <div style="font-weight:500;color:#1a6b52;margin-bottom:6px">Loyalty discounts</div>
                    <div style="color:#166534;display:flex;flex-direction:column;gap:4px">
                        <div>🎁 Pay 6 months → get 1 month free</div>
                        <div>🎁 Pay 12 months → get 2 months free</div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 18px;max-width:700px;font-size:13px">
                    <div style="font-weight:500;margin-bottom:6px">How to upgrade or renew</div>
                    <div style="color:#8a8880;line-height:1.7;margin-bottom:10px">Send your payment via M-Pesa and contact us. We activate accounts within minutes during business hours.</div>
                    <div style="display:flex;gap:20px;flex-wrap:wrap">
                        <div>📞 <strong>{{ config('app.support_phone','0700 000 000') }}</strong></div>
                        <div>✉ <strong>{{ config('app.support_email','support@nyumba.co.ke') }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- ── Danger zone ── --}}
            <div id="panel-danger" class="sp" style="display:none">
                <div style="font-size:15px;font-weight:500;margin-bottom:6px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    Danger zone
                </div>

                <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:20px;max-width:560px">
                    <div style="display:flex;align-items:flex-start;gap:14px">
                        <div style="font-size:22px;flex-shrink:0;margin-top:2px">⚠️</div>
                        <div>
                            <div style="font-size:14px;font-weight:600;color:#991b1b;margin-bottom:6px">Reset account data</div>
                            <div style="font-size:13px;color:#7f1d1d;line-height:1.6;margin-bottom:16px">
                                This permanently deletes all properties, units, tenants, leases, invoices, payments, expenses,
                                maintenance requests, utility data, SMS logs, notifications and audit logs.
                                <br><br>
                                <strong>Your account, users and subscription are kept.</strong>
                                This action cannot be undone.
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
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Add a user</div>
            <button onclick="document.getElementById('invite-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.users.invite') }}">
            @csrf
            <div style="display:grid;gap:13px;margin-bottom:18px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Full name</label>
                    <input name="name" type="text" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Email address</label>
                    <input name="email" type="email" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Phone number</label>
                    <input name="phone" type="text" required placeholder="07XX" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Role</label>
                    <select name="role" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="owner">Owner (full access)</option>
                        <option value="read_only">Read only (view only)</option>
                    </select>
                </div>
            </div>
            <div style="background:#f5f4f0;border-radius:7px;padding:10px 12px;font-size:12px;color:#8a8880;margin-bottom:16px">
                A temporary password of <strong>password123</strong> will be assigned.
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

<script>
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

function switchType(type) {
    const isPaybill = type === 'paybill';
    document.getElementById('fields-paybill').style.display = isPaybill ? 'grid' : 'none';
    document.getElementById('fields-till').style.display    = isPaybill ? 'none' : 'grid';
    const lp = document.getElementById('label-paybill');
    const lt = document.getElementById('label-till');
    lp.style.borderColor = isPaybill ? '#1a6b52' : 'rgba(0,0,0,0.1)';
    lp.style.background  = isPaybill ? '#f0fdf4' : '#fff';
    lt.style.borderColor = isPaybill ? 'rgba(0,0,0,0.1)' : '#1a6b52';
    lt.style.background  = isPaybill ? '#fff' : '#f0fdf4';
}

// Fix nav pill borders on desktop (no borders)
function fixNavBorders() {
    if (window.innerWidth > 700) {
        document.querySelectorAll('.sni').forEach(n => n.style.border = 'none');
        const active = document.querySelector('.sni[style*="#e6f2ed"]');
        if (active) active.style.border = 'none';
    }
}
window.addEventListener('resize', fixNavBorders);
fixNavBorders();
</script>

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
            You are about to permanently delete <strong>all business data</strong> in this account.
            This includes all properties, tenants, invoices, payments and everything else.
            <br><br>
            This <strong>cannot be undone</strong>.
        </div>

        <form method="POST" action="{{ route('settings.reset-account') }}" id="reset-form">
            @csrf
            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:500;color:#991b1b;margin-bottom:8px">
                    Type <strong>RESET</strong> to confirm:
                </label>
                <input type="text"
                       name="confirmation"
                       id="reset-confirmation"
                       placeholder="RESET"
                       autocomplete="off"
                       oninput="checkResetInput(this)"
                       style="width:100%;height:40px;padding:0 14px;border:2px solid #fca5a5;border-radius:7px;font-size:15px;font-family:'DM Sans',sans-serif;outline:none;letter-spacing:.08em;text-transform:uppercase">
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        id="reset-submit-btn"
                        disabled
                        style="padding:8px 20px;background:#e5e7eb;color:#9ca3af;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:not-allowed;font-family:'DM Sans',sans-serif;transition:all .15s"
                        onclick="return confirm('Last chance — this will permanently delete all data. Are you absolutely sure?')">
                    Yes, reset everything
                </button>
                <button type="button"
                        onclick="closeResetModal()"
                        style="padding:8px 15px;background:transparent;color:#6b7280;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closeResetModal() {
    document.getElementById('reset-modal').style.display = 'none';
    document.getElementById('reset-confirmation').value  = '';
    var btn = document.getElementById('reset-submit-btn');
    btn.disabled          = true;
    btn.style.background  = '#e5e7eb';
    btn.style.color       = '#9ca3af';
    btn.style.cursor      = 'not-allowed';
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