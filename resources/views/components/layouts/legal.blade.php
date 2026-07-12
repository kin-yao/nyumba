<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'Legal' }} — Nyumba</title>
<meta name="robots" content="index, follow">
<link rel="preconnect" href="https://api.fontshare.com" crossorigin>
<link href="https://api.fontshare.com/v2/css?f[]=clash-display@500,600&f[]=satoshi@400,500,700&display=swap" rel="stylesheet">

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-P5FWPX45');</script>
<!-- End Google Tag Manager -->

<style>
:root{
  --ink:#14110f; --ink-2:#2b2722;
  --paper:#f4f2ec; --card:#fff;
  --green:#1a6b52; --green-deep:#0e3f30;
  --line:#e5e1d6; --mute:#857f73;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Satoshi',sans-serif;background:var(--paper);color:var(--ink-2);line-height:1.65}
.legal-topbar{
  background:var(--green-deep);
  padding:16px clamp(16px,4vw,40px);
  display:flex;align-items:center;justify-content:space-between;
}
.legal-logo{background:#fff;border-radius:8px;padding:6px 10px;display:inline-flex}
.legal-logo img{height:22px;width:auto;display:block}
.legal-back{color:rgba(255,255,255,.75);text-decoration:none;font-size:13px;font-weight:500}
.legal-back:hover{color:#fff}
.legal-wrap{max-width:760px;margin:0 auto;padding:clamp(28px,6vw,56px) clamp(16px,4vw,24px) 80px}
.legal-title{font-family:'Clash Display',sans-serif;font-size:clamp(26px,4vw,36px);font-weight:600;color:var(--ink);margin-bottom:8px}
.legal-meta{font-size:13px;color:var(--mute);margin-bottom:36px}
.legal-doc h2{font-family:'Clash Display',sans-serif;font-size:19px;font-weight:600;color:var(--ink);margin:32px 0 12px}
.legal-doc h2:first-child{margin-top:0}
.legal-doc h3{font-size:15px;font-weight:700;color:var(--ink);margin:20px 0 8px}
.legal-doc p{font-size:14.5px;margin-bottom:14px}
.legal-doc ul, .legal-doc ol{font-size:14.5px;margin:0 0 14px 22px}
.legal-doc li{margin-bottom:7px}
.legal-doc strong{color:var(--ink)}
.legal-doc a{color:var(--green)}
.legal-note{
  background:var(--card);border:1px solid var(--line);border-left:3px solid var(--green);
  border-radius:8px;padding:14px 16px;font-size:13px;color:var(--mute);margin-bottom:32px;
}
.legal-footer{
  border-top:1px solid var(--line);margin-top:48px;padding-top:24px;
  font-size:13px;color:var(--mute);
}
.legal-footer a{color:var(--green);text-decoration:none;font-weight:500}
</style>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P5FWPX45"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div class="legal-topbar">
    <a href="{{ url('/') }}" class="legal-logo"><img src="/images/logo.png" alt="Nyumba"></a>
    <a href="{{ url('/') }}" class="legal-back">&larr; Back to home</a>
</div>

<div class="legal-wrap">
    <div class="legal-title">{{ $title }}</div>
    <div class="legal-meta">Last updated: {{ $lastUpdated ?? 'July 2026' }}</div>

    <div class="legal-note">
        This document is provided as general information about how Nyumba operates and is not a substitute for independent legal advice. If you need this reviewed for a specific legal or regulatory purpose, please consult a qualified advocate.
    </div>

    <div class="legal-doc">
        {{ $slot }}
    </div>

    <div class="legal-footer">
        Questions about this document? Contact us on WhatsApp: <a href="https://wa.me/254705056343">+254 705 056 343</a>
        &middot; <a href="{{ route('terms') }}">Terms of Service</a>
        &middot; <a href="{{ route('privacy') }}">Privacy Policy</a>
    </div>
</div>
</body>
</html>