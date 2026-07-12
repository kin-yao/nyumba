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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nyumba — Collect rent, not excuses</title>
<meta name="description" content="Automated invoicing, M-Pesa reconciliation, utility billing and SMS reminders for Kenyan rental portfolios.">

<link rel="preconnect" href="https://api.fontshare.com" crossorigin>
<link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600&f[]=satoshi@400,500,700&f[]=cabinet-grotesk@500,700&display=swap" rel="stylesheet">

<style>
:root{
  --ink:#14110f; --ink-2:#2b2722;
  --paper:#f4f2ec; --paper-2:#ebe7dd; --card:#fff;
  --green:#1a6b52; --green-deep:#0e3f30; --green-soft:#e4efe9;
  --gold:#c2924f; --line:#ddd6c9; --mute:#857f73;
  --shadow:0 24px 60px -28px rgba(20,17,15,.32);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;overflow-x:hidden;width:100%}
body{font-family:'Satoshi',sans-serif;background:var(--paper);color:var(--ink);line-height:1.55;overflow-x:hidden;-webkit-font-smoothing:antialiased}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}

.display{font-family:'Clash Display',serif;font-weight:600;letter-spacing:-.025em;line-height:.96}
.eyebrow{font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:var(--green)}
.eyebrow.light{color:rgba(244,242,236,.55)}
.wrap{max-width:1280px;margin:0 auto;padding:0 24px}

.btn{display:inline-flex;align-items:center;gap:10px;padding:14px 24px;border-radius:4px;border:none;cursor:pointer;font-family:'Satoshi',sans-serif;font-size:14px;font-weight:700;transition:transform .2s,background .2s,color .2s}
.btn .arr{width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;background:rgba(255,255,255,.2)}
.btn-green{background:var(--green);color:#fff}.btn-green:hover{background:var(--green-deep)}
.btn-gold{background:var(--gold);color:var(--ink)}.btn-gold:hover{transform:translateY(-2px)}
.btn-light{background:rgba(244,242,236,.12);color:var(--paper);box-shadow:inset 0 0 0 1px rgba(244,242,236,.25)}.btn-light:hover{background:rgba(244,242,236,.22)}

/* Utility bar */
.util{background:var(--ink);color:rgba(244,242,236,.72);font-size:12.5px}
.util-inner{max-width:1280px;margin:0 auto;padding:9px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.util-left{display:flex;gap:26px;align-items:center}
.util-left .it{display:flex;gap:8px;align-items:center}
.util-left .lbl{color:rgba(244,242,236,.4)}.util-left b{color:var(--paper);font-weight:500}
.util-right{display:flex;gap:10px;align-items:center;color:rgba(244,242,236,.5)}
.util-right .dot{width:5px;height:5px;border-radius:50%;background:var(--gold)}

/* Nav */
.nav{background:var(--card);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:60}
.nav-inner{max-width:1280px;margin:0 auto;padding:0 24px;height:72px;display:flex;align-items:center;justify-content:space-between}
.nav-logo img{height:34px;width:auto;object-fit:contain}
.nav-links{display:flex;gap:34px}
.nav-links a{font-size:14px;font-weight:500;color:var(--ink-2);position:relative;padding:4px 0}
.nav-links a::after{content:"";position:absolute;left:0;right:100%;bottom:-2px;height:2px;background:var(--green);transition:right .28s}
.nav-links a:hover::after{right:0}
.nav-cta{display:flex;gap:10px;align-items:center}
.nav-cta .btn{padding:10px 18px;font-size:13px}
.nav-burger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:6px;z-index:70}
.nav-burger span{width:22px;height:2px;background:var(--ink);display:block;transition:transform .25s,opacity .25s}
.nav-burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.nav-burger.open span:nth-child(2){opacity:0}
.nav-burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}

/* Hero */
.hero{position:relative;background:var(--ink);overflow:hidden}
.hero-photo{position:absolute;inset:0}
.hero-photo img{width:100%;height:100%;object-fit:cover;opacity:.34}
.hero-photo::after{content:"";position:absolute;inset:0;background:linear-gradient(105deg,var(--ink) 30%,rgba(20,17,15,.55) 60%,rgba(20,17,15,.78))}
.shard{position:absolute;top:0;bottom:0;pointer-events:none}
.shard.a{left:-6%;width:46%;background:var(--green);opacity:.92;clip-path:polygon(0 0,72% 0,40% 100%,0 100%)}
.shard.b{left:8%;width:44%;background:var(--green-deep);opacity:.85;clip-path:polygon(18% 0,60% 0,30% 100%,0 100%)}
.shard.c{left:30%;width:30%;background:var(--gold);opacity:.16;clip-path:polygon(40% 0,72% 0,40% 100%,12% 100%)}
.hero-inner{position:relative;z-index:2;max-width:1280px;margin:0 auto;padding:92px 24px 150px}
.hero-badge{display:inline-flex;align-items:center;gap:9px;background:rgba(244,242,236,.1);border:1px solid rgba(244,242,236,.2);color:var(--paper);padding:8px 16px;border-radius:3px;font-family:'Cabinet Grotesk',sans-serif;font-weight:500;font-size:11px;letter-spacing:.16em;text-transform:uppercase;margin-bottom:26px}
.hero-badge b{width:6px;height:6px;border-radius:50%;background:var(--gold)}
.hero h1{color:var(--paper);font-size:clamp(46px,7vw,92px);margin-bottom:24px;max-width:14ch}
.hero h1 em{font-style:italic;font-weight:400;color:#9ed8c2}
.hero-sub{font-size:16px;line-height:1.7;color:rgba(244,242,236,.7);max-width:460px;margin-bottom:34px}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap}

/* Feature cards */
.fcards-band{margin-top:-88px;position:relative;z-index:5}
.fcards{max-width:1280px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
.fcard{background:var(--card);border-radius:8px;padding:26px 20px;box-shadow:var(--shadow);border-top:3px solid transparent;transition:transform .26s,border-color .26s}
.fcard:hover{transform:translateY(-8px);border-top-color:var(--green)}
.fcard .ic{width:46px;height:46px;border-radius:8px;background:var(--green-soft);display:flex;align-items:center;justify-content:center;margin-bottom:18px;color:var(--green)}
.fcard h4{font-size:15px;font-weight:700;margin-bottom:6px;letter-spacing:-.01em}
.fcard p{font-size:12.5px;color:var(--mute);line-height:1.5}

/* Trust */
.trust{padding:56px 0 10px}
.trust-inner{display:flex;align-items:center;gap:40px;flex-wrap:wrap;justify-content:space-between}
.trust-lead{font-family:'Clash Display',serif;font-weight:500;font-size:18px;max-width:280px;line-height:1.25}
.trust-lead span{color:var(--green)}
.trust-stats{display:flex;flex-wrap:wrap}
.tstat{padding:6px 32px;border-left:1px solid var(--line)}
.tstat:first-child{border-left:none;padding-left:0}
.tstat .n{font-family:'Clash Display',serif;font-weight:600;font-size:34px;letter-spacing:-.03em;line-height:1}
.tstat .l{font-family:'Cabinet Grotesk',sans-serif;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--mute);margin-top:5px}

/* About */
.about{padding:90px 0;overflow:hidden}
.about-grid{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
.about-visual{position:relative;padding:20px 0 20px 20px}
.about-visual .deco{position:absolute;left:-6px;top:-14px;width:120px;height:120px;display:grid;grid-template-columns:repeat(3,1fr);gap:6px;z-index:0}
.about-visual .deco i{background:var(--green);opacity:.16;border-radius:2px;transform:rotate(45deg)}
.about-visual .deco i:nth-child(odd){opacity:.32}
.about-photo{position:relative;z-index:1;border-radius:10px;overflow:hidden;aspect-ratio:5/4;box-shadow:var(--shadow);background:var(--paper-2)}
.about-photo img{width:100%;height:100%;object-fit:cover}
.about-play{position:absolute;right:-22px;bottom:30px;z-index:2;width:64px;height:64px;border-radius:50%;background:var(--green);color:#fff;border:none;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 14px 30px -10px rgba(26,107,82,.6);transition:transform .2s}
.about-play:hover{transform:scale(1.06)}
.about h2{font-size:clamp(32px,4vw,52px);margin:14px 0 20px;max-width:14ch}
.about h2 em{font-style:italic;font-weight:400;color:var(--green)}
.about-body{font-size:15px;color:var(--ink-2);line-height:1.7;border-left:2px solid var(--green);padding-left:18px;margin-bottom:26px;max-width:440px}
.about-points{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:30px;max-width:440px}
.apoint{display:flex;gap:12px;align-items:flex-start}
.apoint .ic{width:40px;height:40px;flex-shrink:0;border-radius:8px;background:var(--green-soft);display:flex;align-items:center;justify-content:center;color:var(--green)}
.apoint h5{font-size:14px;font-weight:700;margin-bottom:2px}
.apoint p{font-size:12.5px;color:var(--mute);line-height:1.45}
.about-cta{display:flex;align-items:center;gap:22px;flex-wrap:wrap}
.about-phone{display:flex;align-items:center;gap:11px}
.about-phone .pic{width:42px;height:42px;border-radius:50%;background:var(--ink);color:var(--gold);display:flex;align-items:center;justify-content:center}
.about-phone .lbl{font-size:11px;color:var(--mute);font-family:'Cabinet Grotesk',sans-serif;letter-spacing:.1em;text-transform:uppercase}
.about-phone .num{font-family:'Clash Display',serif;font-weight:600;font-size:19px}

/* Services */
.svc{position:relative;background:var(--paper-2);padding:88px 0 100px;overflow:hidden}
.svc::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,transparent 0 48%,rgba(255,255,255,.5) 48% 52%,transparent 52%),linear-gradient(135deg,transparent 0 73%,rgba(26,107,82,.05) 73% 76%,transparent 76%);pointer-events:none}
.svc-head{text-align:center;position:relative;z-index:1;margin-bottom:50px}
.svc-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:14px}
.svc-eyebrow .dash{width:26px;height:1px;background:var(--green)}
.svc-head h2{font-size:clamp(30px,4vw,48px);max-width:16ch;margin:0 auto}
.svc-head h2 em{font-style:italic;font-weight:400;color:var(--green)}
.svc-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.scard{background:var(--card);border-radius:10px;padding:32px 24px 28px;text-align:center;transition:transform .26s,box-shadow .26s;box-shadow:0 1px 0 rgba(20,17,15,.04)}
.scard:hover{transform:translateY(-10px);box-shadow:var(--shadow)}
.scard .ic{width:62px;height:62px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 18px}
.scard h4{font-size:17px;font-weight:700;margin-bottom:8px}
.scard .uline{width:34px;height:3px;background:var(--gold);margin:0 auto 14px;border-radius:2px}
.scard p{font-size:13px;color:var(--mute);line-height:1.6}

/* Pricing */
.pricing{padding:90px 0 100px;background:var(--ink);color:var(--paper)}
.pricing-head{text-align:center;margin-bottom:48px}
.pricing-head .eyebrow{display:inline-block;margin-bottom:14px}
.pricing-head h2{font-size:clamp(30px,4vw,52px);color:var(--paper);max-width:18ch;margin:0 auto}
.pricing-head h2 em{font-style:italic;font-weight:400;color:#9ed8c2}
.pricing-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.pc{background:rgba(244,242,236,.05);border:1px solid rgba(244,242,236,.1);border-radius:12px;padding:30px 26px;display:flex;flex-direction:column;transition:transform .26s,border-color .26s}
.pc:hover{transform:translateY(-8px);border-color:rgba(244,242,236,.3)}
.pc.hot{background:var(--green);border-color:var(--green)}
.pc .pname{font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:rgba(244,242,236,.5);margin-bottom:18px;display:flex;justify-content:space-between;align-items:center}
.pc.hot .pname{color:rgba(255,255,255,.78)}
.pc .badge{background:var(--gold);color:var(--ink);font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;padding:4px 10px;border-radius:999px}
.pc .amt{font-family:'Clash Display',serif;font-weight:600;font-size:42px;letter-spacing:-.03em;line-height:.9;color:var(--paper)}
.pc .per{font-family:'Cabinet Grotesk',sans-serif;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(244,242,236,.4);margin:10px 0 16px}
.pc .desc{font-size:13px;color:rgba(244,242,236,.6);line-height:1.55;margin-bottom:20px;min-height:62px}
.pc.hot .desc{color:rgba(255,255,255,.85)}
.pc ul{list-style:none;margin-bottom:24px;display:flex;flex-direction:column}
.pc li{font-size:13.5px;color:rgba(244,242,236,.78);display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid rgba(244,242,236,.1)}
.pc.hot li{color:rgba(255,255,255,.92);border-bottom-color:rgba(255,255,255,.16)}
.pc li svg{flex-shrink:0;color:var(--gold)}
.pc .pcta{display:block;text-align:center;padding:14px;border-radius:6px;font-size:14px;font-weight:700;margin-top:auto;transition:all .2s;background:rgba(244,242,236,.1);color:var(--paper)}
.pc .pcta:hover{background:var(--paper);color:var(--ink)}
.pc.hot .pcta{background:var(--gold);color:var(--ink)}.pc.hot .pcta:hover{background:#fff}
.pricing-note{text-align:center;margin-top:30px;font-family:'Clash Display',serif;font-style:italic;font-size:16px;color:rgba(244,242,236,.45)}
.pricing-note b{color:var(--gold);font-style:normal;font-weight:600}

/* Contact CTA */
.contact{padding:90px 0}
.contact-card{background:var(--green-deep);border-radius:16px;padding:64px 48px;color:var(--paper);position:relative;overflow:hidden}
.contact-card .shard{position:absolute;top:0;bottom:0;right:-4%;width:30%;background:var(--green);opacity:.4;clip-path:polygon(40% 0,100% 0,100% 100%,8% 100%)}
.contact-inner{position:relative;z-index:1;display:grid;grid-template-columns:1.3fr 1fr;gap:48px;align-items:center}
.contact h2{font-size:clamp(30px,4vw,48px);color:var(--paper);margin-bottom:14px;max-width:16ch}
.contact h2 em{font-style:italic;font-weight:400;color:var(--gold)}
.contact p{color:rgba(244,242,236,.7);font-size:15px;margin-bottom:28px;max-width:44ch}
.contact-actions{display:flex;gap:14px;flex-wrap:wrap}
.contact-methods{display:flex;flex-direction:column;gap:18px}
.cm{display:flex;gap:14px;align-items:center}
.cm .ic{width:46px;height:46px;flex-shrink:0;border-radius:10px;background:rgba(244,242,236,.1);color:var(--gold);display:flex;align-items:center;justify-content:center}
.cm .t{font-size:11px;font-family:'Cabinet Grotesk',sans-serif;letter-spacing:.12em;text-transform:uppercase;color:rgba(244,242,236,.5)}
.cm .v{font-family:'Clash Display',serif;font-weight:600;font-size:18px}

/* Footer */
.foot{background:var(--ink);color:var(--paper);padding:64px 0 28px}
.foot-grid{display:grid;grid-template-columns:1.8fr 1fr 1fr 1.2fr;gap:40px;padding-bottom:40px;border-bottom:1px solid rgba(244,242,236,.12)}
.foot-logo{background:#fff;border-radius:6px;padding:6px 10px;display:inline-block;margin-bottom:16px}
.foot-logo img{height:26px;width:auto}
.foot-desc{font-family:'Clash Display',serif;font-weight:400;font-size:17px;line-height:1.4;color:rgba(244,242,236,.5);max-width:260px}
.foot-h{font-family:'Cabinet Grotesk',sans-serif;font-weight:700;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin-bottom:18px}
.foot-a{display:block;font-size:14px;color:rgba(244,242,236,.6);margin-bottom:11px;transition:color .2s,padding .2s}
.foot-a:hover{color:var(--paper);padding-left:4px}
.foot-bottom{max-width:1280px;margin:22px auto 0;padding:0 24px;display:flex;justify-content:space-between;font-size:12px;letter-spacing:.04em;color:rgba(244,242,236,.32)}

/* Reveal + modal */
.rv{opacity:0;transform:translateY(22px);transition:opacity .8s cubic-bezier(.2,.7,.2,1),transform .8s cubic-bezier(.2,.7,.2,1)}
.rv.in{opacity:1;transform:none}
.demo{display:none;position:fixed;inset:0;background:rgba(14,11,9,.86);z-index:200;align-items:center;justify-content:center;padding:20px}
.demo.open{display:flex}
.demo-box{background:var(--ink);border-radius:10px;width:100%;max-width:840px;overflow:hidden;position:relative}
.demo-x{position:absolute;top:14px;right:14px;width:34px;height:34px;border-radius:50%;background:rgba(244,242,236,.12);border:none;color:var(--paper);font-size:18px;cursor:pointer;z-index:2}

@media(max-width:1040px){.fcards{grid-template-columns:repeat(3,1fr)}.svc-grid{grid-template-columns:repeat(2,1fr)}.pricing-grid{grid-template-columns:1fr 1fr}}
@media(max-width:980px){.foot-grid{grid-template-columns:1fr 1fr;gap:30px}.util-left .it.hide-sm{display:none}.contact-inner{grid-template-columns:1fr;gap:36px}}
@media(max-width:860px){.about-grid{grid-template-columns:1fr;gap:44px}.about-visual{max-width:460px}}
@media(max-width:760px){.nav-burger{display:flex}.nav-cta .btn-gold{display:none}.nav-links{display:none;position:absolute;top:100%;left:0;right:0;flex-direction:column;gap:0;background:var(--card);border-bottom:1px solid var(--line);box-shadow:0 16px 30px -18px rgba(20,17,15,.3);padding:6px 24px 14px}.nav-links.open{display:flex}.nav-links a{padding:14px 2px;border-bottom:1px solid var(--line);font-size:15px}.nav-links a:last-child{border-bottom:none}.nav-links a::after{display:none}.util-right{display:none}.foot-grid{grid-template-columns:1fr 1fr}.foot-bottom{flex-direction:column;gap:6px;text-align:center}}
@media(max-width:680px){.fcards-band{margin-top:-60px}.fcards{grid-template-columns:1fr 1fr}.trust-stats{display:grid;grid-template-columns:repeat(2,auto);justify-content:start;gap:18px 28px;width:100%}.tstat{padding:0;border-left:none}.about-points{grid-template-columns:1fr}.svc-grid{grid-template-columns:1fr}.pricing-grid{grid-template-columns:1fr}.contact-card{padding:40px 26px}}
@media(max-width:480px){.foot-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P5FWPX45"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Nav -->
<nav class="nav">
  <div class="nav-inner">
    <a href="/" class="nav-logo"><img src="/images/logo.png" alt="Nyumba"></a>
    <div class="nav-links" id="navLinks">
      <a href="#features">Features</a>
      <a href="#about">Why Nyumba</a>
      <a href="#pricing">Pricing</a>
      <a href="#contact">Contact</a>
      <a href="/login">Sign in</a>
    </div>
    <div class="nav-cta">
      <a href="/login" class="btn btn-gold">Sign in</a>
      <a href="/register/step1" class="btn btn-green">Start free <span class="arr">&rarr;</span></a>
    </div>
    <button class="nav-burger" id="navBurger" aria-label="Menu" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-photo">
    <img src="/images/hero.jpg" alt="Apartment block in Nairobi">
  </div>
  <div class="shard a"></div><div class="shard b"></div><div class="shard c"></div>
  <div class="hero-inner rv">
    <h1 class="display">Collect rent,<br>not <em>excuses</em>.</h1>
    <p class="hero-sub">Invoicing, M-Pesa reconciliation, utility billing and SMS reminders — running automatically for every unit in the portfolio.</p>
    <div class="hero-actions">
      <a href="/register/step1" class="btn btn-gold">Start free trial <span class="arr" style="background:rgba(20,17,15,.15)">&rarr;</span></a>
      <button class="btn btn-light" onclick="openDemo()"><span class="arr" style="background:var(--green)">&#9654;</span> Watch demo</button>
    </div>
  </div>
</section>

<!-- FEATURE CARDS -->
<div class="fcards-band" id="features">
  <div class="fcards">
    @foreach([
      ['Automated invoicing','PDF invoices built and sent on schedule.','M3 7h14M5 3h10l2 4v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4a1 1 0 011-1z'],
      ['M-Pesa reconciliation','Payments matched to invoices instantly.','M12 1v22M5 6h10a3 3 0 010 6H7a3 3 0 000 6h10'],
      ['SMS reminders','Arrears chased before they grow.','M4 4h16v12H8l-4 4V4z'],
      ['Utility billing','Meter readings to billed amounts.','M4 20V10M10 20V4M16 20v-7M22 20H2'],
      ['Audit trail','Every action timestamped and traceable.','M9 11l3 3 8-8M21 12a9 9 0 11-6.2-8.5'],
    ] as [$t,$d,$path])
      <div class="fcard rv">
        <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg></div>
        <h4>{{ $t }}</h4><p>{{ $d }}</p>
      </div>
    @endforeach
  </div>
</div>

<!-- TRUST -->
<section class="trust">
  <div class="wrap">
    <div class="trust-inner rv">
      <div class="trust-lead">Trusted across Kenyan rental <span>portfolios</span> of every size.</div>
      <div class="trust-stats">
        <div class="tstat"><div class="n">500+</div><div class="l">Landlords</div></div>
        <div class="tstat"><div class="n">12k+</div><div class="l">Units live</div></div>
        <div class="tstat"><div class="n">47</div><div class="l">Counties</div></div>
        <div class="tstat"><div class="n">30 day</div><div class="l">Free trial</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
  <div class="wrap">
    <div class="about-grid">
      <div class="about-visual rv">
        <div class="deco">@for($i=0;$i<9;$i++)<i></i>@endfor</div>
        <div class="about-photo"><img src="/images/about.jpg" alt="Reviewing a rental portfolio"></div>
        <button class="about-play" onclick="openDemo()">&#9654;</button>
      </div>
      <div class="rv">
        <div class="eyebrow">Why Nyumba</div>
        <h2 class="display">Built for the realities of <em>Kenyan property</em>.</h2>
        <p class="about-body">Rent rarely arrives on time, and never in one format. Nyumba reconciles M-Pesa, Till and bank payments against every invoice, then chases arrears by SMS — without manual follow-up.</p>
        <div class="about-points">
          <div class="apoint">
            <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M5 6h10a3 3 0 010 6H7a3 3 0 000 6h10"/></svg></div>
            <div><h5>Payment matching</h5><p>M-Pesa and bank inflows reconciled to invoices.</p></div>
          </div>
          <div class="apoint">
            <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg></div>
            <div><h5>Caretaker ready</h5><p>Meter readings entered from any phone.</p></div>
          </div>
        </div>
        <div class="about-cta">
          <a href="/register/step1" class="btn btn-green">Start free trial <span class="arr">&rarr;</span></a>
          <div class="about-phone">
            <div class="pic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.6A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.4 1.8.7 2.7a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.4-1.2a2 2 0 012.1-.4c.9.3 1.8.6 2.7.7a2 2 0 011.7 2z"/></svg></div>
            <div><div class="lbl">Talk to support</div><div class="num">0705 056 343</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="svc">
  <div class="wrap">
    <div class="svc-head rv">
      <div class="svc-eyebrow"><span class="dash"></span><span class="eyebrow">What it handles</span><span class="dash"></span></div>
      <h2 class="display">Everything a rental portfolio <em>needs</em>.</h2>
    </div>
    <div class="svc-grid">
      @foreach([
        ['Residential & commercial','Bedsitters to godowns, billed on their own terms.','M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6'],
        ['Tenant & lease tracking','Deposits, lease terms and balances in one ledger.','M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.9M16 3.1a4 4 0 010 7.8'],
        ['Financial reporting','Collection rate, occupancy and net income on demand.','M3 3v18h18M7 14l4-4 4 4 4-6'],
        ['Maintenance requests','Issues logged, assigned and closed with a record.','M14.7 6.3a4 4 0 01-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 015.4-5.4l-3 3-2-2 3-3z'],
      ] as [$t,$d,$path])
        <div class="scard rv">
          <div class="ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg></div>
          <h4>{{ $t }}</h4><div class="uline"></div><p>{{ $d }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="pricing" id="pricing">
  <div class="wrap">
    <div class="pricing-head rv">
      <div class="eyebrow light">Pricing</div>
      <h2 class="display">Pricing that <em>pays for itself</em>.</h2>
    </div>
    <div class="pricing-grid">
      @php
        $plans = [
          ['Starter','KES 2,300','per month','For the new landlord moving off notebooks and onto schedule.',['Up to 20 units','80 SMS credits monthly','PDF invoices','Utility tracking'],false],
          ['Growth','KES 4,600','per month','For the growing portfolio that should run without daily oversight.',['Up to 50 units','200 SMS credits monthly','Bulk invoicing','Advanced reports'],true],
          ['Pro','KES 7,500','per month','For multiple buildings, caretaker teams and co-owners.',['Up to 100 units','400 SMS credits monthly','Multi-user access','Full audit trail'],false],
          ['Enterprise','Custom','tailored to portfolio','For managers running 100+ units across many owners.',['Unlimited units','Custom SMS bundle','Dedicated support','API access'],false],
        ];
      @endphp
      @foreach($plans as [$name,$amt,$per,$desc,$feats,$hot])
        <div class="pc {{ $hot ? 'hot' : '' }} rv">
          <div class="pname">{{ $name }} @if($hot)<span class="badge">Popular</span>@endif</div>
          <div class="amt" @if($name==='Enterprise') style="font-size:32px;padding-top:8px" @endif>{{ $amt }}</div>
          <div class="per">{{ $per }}</div>
          <p class="desc">{{ $desc }}</p>
          <ul>
            @foreach($feats as $f)
              <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>{{ $f }}</li>
            @endforeach
          </ul>
          <a href="{{ $name==='Enterprise' ? 'mailto:hello@nyumba.co.ke' : '/register/step1' }}" class="pcta">{{ $name==='Enterprise' ? 'Talk to support' : 'Start free trial' }}</a>
        </div>
      @endforeach
    </div>
    <p class="pricing-note">Pay 6 months, get 1 free. <b>Pay 12 months, get 2 free.</b> Extra SMS at KES 1 per credit.</p>
  </div>
</section>

<!-- CONTACT CTA -->
<section class="contact" id="contact">
  <div class="wrap">
    <div class="contact-card rv">
      <div class="shard"></div>
      <div class="contact-inner">
        <div>
          <h2 class="display">Start the <em>free trial</em> today.</h2>
          <p>Thirty days, full features, no card. Properties, units and tenants import from a single CSV, with setup support included.</p>
          <div class="contact-actions">
            <a href="/register/step1" class="btn btn-gold">Create an account <span class="arr" style="background:rgba(20,17,15,.15)">&rarr;</span></a>
            <a href="https://wa.me/254705056343" class="btn btn-light">WhatsApp support</a>
          </div>
        </div>
        <div class="contact-methods">
          <div class="cm">
            <div class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.9-2-1-.3-.1-.5-.1-.7.2l-.9 1.1c-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5 0-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.5 15.3L2 22l4.8-1.3A10 10 0 1012 2z"/></svg></div>
            <div><div class="t">WhatsApp</div><div class="v">0705 056 343</div></div>
          </div>
          <div class="cm">
            <div class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></div>
            <div><div class="t">Email</div><div class="v">hello@nyumba.co.ke</div></div>
          </div>
          <div class="cm">
            <div class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
            <div><div class="t">Office</div><div class="v">Nairobi, Kenya</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="foot">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <a href="/" class="foot-logo"><img src="/images/logo.png" alt="Nyumba"></a>
        <p class="foot-desc">The operating system for Kenyan landlords.</p>
      </div>
      <div>
        <div class="foot-h">Product</div>
        <a href="#features" class="foot-a">Features</a>
        <a href="#pricing" class="foot-a">Pricing</a>
        <a href="/register/step1" class="foot-a">Start free trial</a>
        <a href="/login" class="foot-a">Sign in</a>
      </div>
      <div>
        <div class="foot-h">Company</div>
        <a href="#about" class="foot-a">Why Nyumba</a>
        <a href="{{ route('privacy') }}" class="foot-a">Privacy policy</a>
        <a href="{{ route('terms') }}" class="foot-a">Terms of service</a>
      </div>
      <div>
        <div class="foot-h">Talk to support</div>
        <a href="https://wa.me/254705056343" class="foot-a">WhatsApp · 0705 056 343</a>
        <a href="mailto:hello@nyumba.co.ke" class="foot-a">hello@nyumba.co.ke</a>
        <span class="foot-a">Nairobi, Kenya</span>
      </div>
    </div>
    <div class="foot-bottom">
      <span>&copy; {{ date('Y') }} Nyumba. All rights reserved.</span>
      <span>Built in Nairobi.</span>
    </div>
  </div>
</footer>

<!-- Demo modal -->
<div class="demo" id="demo" onclick="if(event.target===this)closeDemo()">
  <div class="demo-box">
    <button class="demo-x" onclick="closeDemo()">&times;</button>
    <div class="yt-wrap" style="position:relative;width:100%;aspect-ratio:16/9"></div>
  </div>
</div>

<script>
  function openDemo() {
    var box  = document.getElementById('demo');
    var wrap = box.querySelector('.yt-wrap');
    box.classList.add('open');
    if (!wrap.querySelector('iframe')) {
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube.com/embed/vjpOAJsPHL8?autoplay=1';
      f.title = 'Nyumba product walkthrough';
      f.frameBorder = '0';
      f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      f.allowFullscreen = true;
      f.style.cssText = 'position:absolute;inset:0;width:100%;height:100%';
      wrap.appendChild(f);
    }
  }

  function closeDemo() {
    var box  = document.getElementById('demo');
    var wrap = box.querySelector('.yt-wrap');
    box.classList.remove('open');
    wrap.innerHTML = '';
  }

  // Mobile menu toggle
  (function(){
    var burger = document.getElementById('navBurger');
    var links  = document.getElementById('navLinks');
    if (burger && links) {
      burger.addEventListener('click', function() {
        var open = links.classList.toggle('open');
        burger.classList.toggle('open', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      links.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
          links.classList.remove('open');
          burger.classList.remove('open');
          burger.setAttribute('aria-expanded', 'false');
        });
      });
    }
  })();

  // Scroll reveal
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
  }), { threshold: .12 });
  document.querySelectorAll('.rv').forEach((el, i) => {
    el.style.transitionDelay = (i % 4 * 70) + 'ms';
    io.observe(el);
  });

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDemo(); });
</script>
</body>
</html>