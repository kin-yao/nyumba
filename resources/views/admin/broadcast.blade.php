<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Broadcast SMS — Nyumba Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;color:#111110}
        .layout{display:flex;min-height:100vh}
        .main{margin-left:220px;flex:1;padding:32px 40px}
        .page-title{font-family:'DM Serif Display',serif;font-size:26px;margin-bottom:4px}
        .page-sub{font-size:13px;color:#8a8880;margin-bottom:28px}
        .card{background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.07);padding:24px;max-width:640px}
        .card-title{font-size:14px;font-weight:500;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(0,0,0,.06)}
        .form-group{margin-bottom:16px}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input,select,textarea{width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:#fff}
        input{height:36px;padding:0 11px}
        textarea{resize:vertical;line-height:1.5}
        input:focus,select:focus,textarea:focus{border-color:#1a6b52}
        select{height:36px}
        .btn{padding:9px 20px;font-size:13px;font-weight:500;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;border:none;display:inline-flex;align-items:center;gap:6px}
        .btn-green{background:#1a6b52;color:#fff}
        .btn-gray{background:#fff;color:#374151;border:1px solid rgba(0,0,0,.1)}
        .target-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px}
        .target-pill{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;border:1.5px solid rgba(0,0,0,.1);cursor:pointer;background:#fff}
        .target-pill.selected{border-color:#1a6b52;background:#e6f2ed;color:#1a6b52}
        a{text-decoration:none;color:inherit}
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar', ['active' => 'broadcast'])
    <main class="main">

        <div class="page-title">Broadcast SMS</div>
        <div class="page-sub">Send a message to all active landlords or a specific plan tier.</div>

        @if(session('success'))
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:13px;color:#166534;max-width:640px">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:13px;color:#991b1b;max-width:640px">
                {{ session('error') }}
            </div>
        @endif

        {{-- Audience summary --}}
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;max-width:640px">
            @foreach($planCounts as $plan => $count)
                @if($count > 0)
                    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,.07);padding:12px 16px;text-align:center;min-width:90px">
                        <div style="font-family:'DM Serif Display',serif;font-size:22px">{{ $count }}</div>
                        <div style="font-size:11px;color:#8a8880;margin-top:2px">{{ ucfirst($plan) }}</div>
                    </div>
                @endif
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.broadcast.send') }}" id="broadcast-form">
            @csrf
            <div class="card">
                <div class="card-title">Compose broadcast</div>

                <div class="form-group">
                    <label>Target audience</label>
                    <div class="target-pills">
                        @php $targets = ['all' => 'All active', 'trial' => 'Trial only', 'starter' => 'Starter', 'growth' => 'Growth', 'pro' => 'Pro', 'enterprise' => 'Enterprise']; @endphp
                        @foreach($targets as $value => $label)
                            @php $count = $planCounts[$value] ?? 0; @endphp
                            <label class="target-pill {{ $value === 'all' ? 'selected' : '' }}" id="pill-{{ $value }}"
                                   onclick="selectTarget('{{ $value }}')">
                                <input type="radio" name="target" value="{{ $value }}" {{ $value === 'all' ? 'checked' : '' }}
                                       style="display:none">
                                {{ $label }} ({{ $count }})
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" maxlength="320" required
                              placeholder="Type your message here... e.g. Nyumba will be down for maintenance on Sunday 11pm - Monday 2am. We apologize for any inconvenience."
                              oninput="updateCount(this)">{{ old('message') }}</textarea>
                    <div style="display:flex;justify-content:space-between;margin-top:4px">
                        <div style="font-size:11px;color:#8a8880">Keep under 160 characters for 1 SMS credit per recipient.</div>
                        <div id="char-count" style="font-size:11px;color:#8a8880">0 / 320</div>
                    </div>
                </div>

                <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px 14px;margin-bottom:20px;font-size:13px;color:#92400e">
                    ⚠ This will send a real SMS to every active landlord in the selected tier.
                    Use the test option below before sending to everyone.
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="submit" class="btn btn-green"
                            onclick="return confirm('Send this SMS to all recipients in the selected tier? This cannot be undone.')">
                        Send broadcast
                    </button>
                </div>
            </div>

            {{-- Test send --}}
            <div class="card" style="margin-top:16px">
                <div class="card-title">Test send</div>
                <div style="font-size:13px;color:#8a8880;margin-bottom:14px">
                    Send the message above to a specific number to preview before broadcasting.
                </div>
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <div style="flex:1">
                        <label>Test phone number</label>
                        <input type="text" name="test_phone" placeholder="07XX or 254XX">
                    </div>
                    <button type="submit" class="btn btn-gray" formnovalidate
                            onclick="document.getElementById('broadcast-form').querySelector('[name=test_phone]').required=true">
                        Send test
                    </button>
                </div>
            </div>
        </form>

    </main>
</div>

<script>
function selectTarget(value) {
    document.querySelectorAll('.target-pill').forEach(p => p.classList.remove('selected'));
    document.getElementById('pill-' + value).classList.add('selected');
    document.querySelector('input[name=target][value="' + value + '"]').checked = true;
}

function updateCount(el) {
    document.getElementById('char-count').textContent = el.value.length + ' / 320';
}
</script>
</body>
</html>