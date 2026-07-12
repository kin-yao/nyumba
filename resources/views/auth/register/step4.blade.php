<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-P5FWPX45');</script>
<!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your portfolio size — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f4f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 480px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
        .logo { margin-bottom: 8px; display: block; }
        .steps { display: flex; gap: 6px; margin-bottom: 28px; }
        .step { height: 3px; border-radius: 2px; flex: 1; }
        .step.active { background: #1a6b52; }
        .step.done { background: #1a6b52; opacity: 0.4; }
        h1 { font-family: 'DM Serif Display', serif; font-size: 22px; margin-bottom: 6px; }
        .subtitle { font-size: 13px; color: #8a8880; margin-bottom: 24px; }
        .options { display: grid; gap: 8px; margin-bottom: 24px; }
        .option { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; border: 2px solid rgba(0,0,0,0.08); border-radius: 10px; cursor: pointer; transition: all 0.15s; position: relative; }
        .option:hover { border-color: #1a6b52; }
        .option.selected { border-color: #1a6b52; background: #f5fbf9; }
        .option.recommended::after { content: 'RECOMMENDED'; position: absolute; top: -9px; right: 14px; background: #1a6b52; color: #fff; font-size: 9px; font-weight: 600; padding: 2px 8px; border-radius: 10px; letter-spacing: .06em; }
        .option input { display: none; }
        .option-left { display: flex; align-items: center; gap: 12px; }
        .option-icon { font-size: 18px; }
        .option-title { font-size: 13px; font-weight: 500; }
        .option-range { font-size: 11px; color: #8a8880; margin-top: 1px; }
        .option-plan { text-align: right; flex-shrink: 0; }
        .plan-name { font-size: 13px; font-weight: 500; color: #1a6b52; }
        .plan-price { font-size: 11px; color: #8a8880; margin-top: 1px; }
        .trial-note { background: #e6f2ed; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; font-size: 12px; color: #1a6b52; line-height: 1.5; }
        .error { font-size: 12px; color: #b91c1c; margin-bottom: 12px; }
        .btn { width: 100%; height: 42px; background: #1a6b52; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 0.2s; }
        .btn:hover { background: #155c45; }
        .btn:disabled { background: #a0c4b8; cursor: not-allowed; }
        @keyframes nyumba-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        #nyumba-loader {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(245,244,240,0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
    </style>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P5FWPX45"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="nyumba-loader">
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
             style="animation:nyumba-spin 0.9s linear infinite">
            <circle cx="20" cy="20" r="16" stroke="#e5e3de" stroke-width="3"/>
            <path d="M20 4a16 16 0 0116 16" stroke="#1a6b52" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <span id="nyumba-loader-text" style="font-size:13px;color:#1a6b52;font-family:'DM Sans',sans-serif;font-weight:500">
            Setting up your account...
        </span>
    </div>
</div>

<script>
    window.showLoader = function(text) {
        const el    = document.getElementById('nyumba-loader');
        const label = document.getElementById('nyumba-loader-text');
        if (label && text) label.textContent = text;
        el.style.display = 'flex';
    };
    window.hideLoader = function() {
        document.getElementById('nyumba-loader').style.display = 'none';
    };
    window.addEventListener('pageshow', hideLoader);
</script>

<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo" style="height:60px;object-fit:contain">

    <div class="steps">
        <div class="step done"></div>
        <div class="step done"></div>
        <div class="step done"></div>
        <div class="step active"></div>
    </div>

    <h1>How big is your portfolio?</h1>
    <p class="subtitle">Step 4 of 4 — We'll recommend the right plan for you</p>

    <div class="trial-note">
        🎉 All plans start with a <strong>30 day free trial</strong>. No payment required.
    </div>

    @error('unit_range')
        <div class="error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('register.step4.post') }}" onsubmit="showLoader('Setting up your account...')">
        @csrf

        <div class="options">
            <label class="option {{ old('unit_range') === '1-5' ? 'selected' : '' }}">
                <input type="radio" name="unit_range" value="1-5" {{ old('unit_range') === '1-5' ? 'checked' : '' }}>
                <div class="option-left">
                    <div class="option-icon">🏠</div>
                    <div>
                        <div class="option-title">Just starting out</div>
                        <div class="option-range">1 to 5 units</div>
                    </div>
                </div>
                <div class="option-plan">
                    <div class="plan-name">Explore</div>
                    <div class="plan-price">Free · 10 SMS</div>
                </div>
            </label>

            <label class="option {{ old('unit_range') === '6-20' ? 'selected' : '' }}">
                <input type="radio" name="unit_range" value="6-20" {{ old('unit_range') === '6-20' ? 'checked' : '' }}>
                <div class="option-left">
                    <div class="option-icon">🏘</div>
                    <div>
                        <div class="option-title">Small portfolio</div>
                        <div class="option-range">6 to 20 units</div>
                    </div>
                </div>
                <div class="option-plan">
                    <div class="plan-name">Starter</div>
                    <div class="plan-price">KES 2,300/mo</div>
                </div>
            </label>

            <label class="option recommended {{ old('unit_range') === '21-50' ? 'selected' : '' }}">
                <input type="radio" name="unit_range" value="21-50" {{ old('unit_range') === '21-50' ? 'checked' : '' }}>
                <div class="option-left">
                    <div class="option-icon">🏢</div>
                    <div>
                        <div class="option-title">Growing portfolio</div>
                        <div class="option-range">21 to 50 units</div>
                    </div>
                </div>
                <div class="option-plan">
                    <div class="plan-name">Growth</div>
                    <div class="plan-price">KES 4,600/mo</div>
                </div>
            </label>

            <label class="option {{ old('unit_range') === '51-100' ? 'selected' : '' }}">
                <input type="radio" name="unit_range" value="51-100" {{ old('unit_range') === '51-100' ? 'checked' : '' }}>
                <div class="option-left">
                    <div class="option-icon">🏙</div>
                    <div>
                        <div class="option-title">Large portfolio</div>
                        <div class="option-range">51 to 100 units</div>
                    </div>
                </div>
                <div class="option-plan">
                    <div class="plan-name">Pro</div>
                    <div class="plan-price">KES 7,500/mo</div>
                </div>
            </label>

            <label class="option {{ old('unit_range') === '100+' ? 'selected' : '' }}">
                <input type="radio" name="unit_range" value="100+" {{ old('unit_range') === '100+' ? 'checked' : '' }}>
                <div class="option-left">
                    <div class="option-icon">🌆</div>
                    <div>
                        <div class="option-title">Enterprise scale</div>
                        <div class="option-range">100+ units</div>
                    </div>
                </div>
                <div class="option-plan">
                    <div class="plan-name">Enterprise</div>
                    <div class="plan-price">Contact us</div>
                </div>
            </label>
        </div>

        <button type="submit" class="btn" id="btn-submit">Create my account →</button>
    </form>
</div>

<script>
    document.querySelectorAll('.option input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            radio.closest('.option').classList.add('selected');
        });
    });
</script>
</body>
</html>