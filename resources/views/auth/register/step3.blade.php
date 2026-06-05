<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>How will you use Nyumba?</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f4f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 520px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
        .logo { margin-bottom: 8px; display: block; }
        .steps { display: flex; gap: 6px; margin-bottom: 28px; }
        .step { height: 3px; border-radius: 2px; flex: 1; }
        .step.active { background: #1a6b52; }
        .step.done { background: #1a6b52; opacity: 0.4; }
        .step.inactive { background: #ece9e2; }
        h1 { font-family: 'DM Serif Display', serif; font-size: 22px; margin-bottom: 6px; }
        .subtitle { font-size: 13px; color: #8a8880; margin-bottom: 24px; }
        .options { display: grid; gap: 10px; margin-bottom: 20px; }
        .option { display: flex; align-items: flex-start; gap: 14px; padding: 16px; border: 2px solid rgba(0,0,0,0.08); border-radius: 10px; cursor: pointer; transition: all 0.15s; }
        .option:hover { border-color: #1a6b52; background: #f5fbf9; }
        .option.selected { border-color: #1a6b52; background: #e6f2ed; }
        .option input { display: none; }
        .option-icon { font-size: 24px; flex-shrink: 0; margin-top: 2px; }
        .option-title { font-size: 14px; font-weight: 500; margin-bottom: 3px; }
        .option-desc { font-size: 12px; color: #8a8880; line-height: 1.4; }
        .error { font-size: 12px; color: #b91c1c; margin-bottom: 12px; }
        .btn { width: 100%; height: 42px; background: #1a6b52; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 0.2s; }
        .btn:hover { background: #155c45; }
    </style>
</head>
<body>
    <div class="card">
        <img src="/images/logo.png" alt="Nyumba" class="logo" style="height:60px;object-fit:contain">

        <div class="steps">
            <div class="step done"></div>
            <div class="step done"></div>
            <div class="step active"></div>
            <div class="step inactive"></div>
        </div>

        <h1>How will you use Nyumba?</h1>
        <p class="subtitle">Step 3 of 4 — Help us personalise your experience</p>

        @error('use_case')
            <div class="error">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('register.step3.post') }}">
            @csrf

            <div class="options">

                <label class="option {{ old('use_case') === 'own_rental' ? 'selected' : '' }}">
                    <input type="radio" name="use_case" value="own_rental" {{ old('use_case') === 'own_rental' ? 'checked' : '' }}>
                    <div class="option-icon">🏠</div>
                    <div>
                        <div class="option-title">I own rental properties</div>
                        <div class="option-desc">I collect rent directly from my tenants. I want to track payments, generate invoices and manage my units.</div>
                    </div>
                </label>

                <label class="option {{ old('use_case') === 'property_manager' ? 'selected' : '' }}">
                    <input type="radio" name="use_case" value="property_manager" {{ old('use_case') === 'property_manager' ? 'checked' : '' }}>
                    <div class="option-icon">🏢</div>
                    <div>
                        <div class="option-title">I manage properties for others</div>
                        <div class="option-desc">I am a professional property manager handling multiple landlords. I need to manage portfolios and report to owners.</div>
                    </div>
                </label>

                <label class="option {{ old('use_case') === 'utility_billing' ? 'selected' : '' }}">
                    <input type="radio" name="use_case" value="utility_billing" {{ old('use_case') === 'utility_billing' ? 'checked' : '' }}>
                    <div class="option-icon">⚡</div>
                    <div>
                        <div class="option-title">Utility and billing management</div>
                        <div class="option-desc">I primarily need to track water, electricity and other utility consumption and bill tenants accurately each month.</div>
                    </div>
                </label>

                <label class="option {{ old('use_case') === 'commercial' ? 'selected' : '' }}">
                    <input type="radio" name="use_case" value="commercial" {{ old('use_case') === 'commercial' ? 'checked' : '' }}>
                    <div class="option-icon">🏪</div>
                    <div>
                        <div class="option-title">Commercial property management</div>
                        <div class="option-desc">I manage shops, offices, warehouses or mixed commercial spaces and need professional invoicing and lease tracking.</div>
                    </div>
                </label>

                <label class="option {{ old('use_case') === 'mixed' ? 'selected' : '' }}">
                    <input type="radio" name="use_case" value="mixed" {{ old('use_case') === 'mixed' ? 'checked' : '' }}>
                    <div class="option-icon">🏗</div>
                    <div>
                        <div class="option-title">Mixed use portfolio</div>
                        <div class="option-desc">I manage a mix of residential and commercial properties and need a single platform for everything.</div>
                    </div>
                </label>

            </div>

            <button type="submit" class="btn">Continue →</button>
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