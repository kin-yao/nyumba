<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nyumba — The operating system for Kenyan landlords.</title>
<meta name="description" content="Automate rent collection, invoicing, SMS reminders and utility tracking for your Kenyan rental portfolio.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Geist:wght@300;400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#111110;
  --paper:#f5f4f0;
  --paper-2:#ece9e2;
  --green:#1a6b52;
  --green-2:#0f4433;
  --olive:#145c45;
  --moss:#0a2d20;
  --gold:#c8965a;
  --mute:#8a8880;
  --line:#ddd8cf;
  --white:#fff;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Geist',sans-serif;background:var(--paper);color:var(--ink);overflow-x:hidden;line-height:1.5;-webkit-font-smoothing:antialiased}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}

.btn{display:inline-flex;align-items:center;gap:9px;padding:13px 20px;border-radius:999px;font-size:13px;font-weight:500;border:none;cursor:pointer;transition:all .22s;font-family:inherit}
.btn-green{background:var(--green);color:var(--white)}
.btn-green:hover{background:var(--green-2)}
.btn-outline{background:transparent;color:var(--ink);box-shadow:inset 0 0 0 1px rgba(20,18,16,.3)}
.btn-outline:hover{background:var(--ink);color:var(--paper);box-shadow:none}
.btn-ghost-light{background:rgba(245,244,240,.1);color:var(--paper);box-shadow:inset 0 0 0 1px rgba(245,244,240,.2)}
.btn-ghost-light:hover{background:rgba(245,244,240,.2)}
.btn .ic{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.18);display:inline-flex;align-items:center;justify-content:center;font-size:10px}

/* NAV */
nav{position:fixed;top:16px;left:20px;right:20px;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:10px 18px;background:rgba(17,17,16,.9);backdrop-filter:blur(20px);border-radius:999px;color:var(--paper)}
.nav-logo{background:#fff;border-radius:7px;padding:4px 9px;display:flex;align-items:center}
.nav-logo img{height:26px;width:auto;object-fit:contain}
.nav-links{display:flex;gap:26px;font-size:13px}
.nav-links a{opacity:.6;transition:opacity .2s}
.nav-links a:hover{opacity:1}
.nav-cta{display:flex;gap:8px;align-items:center}
.nav-cta .btn{padding:8px 16px;font-size:13px}

/* HERO */
.hero{min-height:100vh;padding:100px 20px 28px;display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:stretch}
.hero-left{display:flex;flex-direction:column;justify-content:space-between;padding:10px 0}
.hero-tag{display:inline-flex;align-items:center;gap:8px;background:var(--ink);color:var(--paper);padding:7px 14px;border-radius:999px;font-family:'Geist Mono';font-size:10px;letter-spacing:.12em;text-transform:uppercase;width:fit-content;margin-bottom:28px}
.hero-tag b{width:6px;height:6px;background:var(--gold);border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{50%{opacity:.3}}
h1.hero-h{font-family:'Fraunces',serif;font-weight:500;font-size:clamp(52px,7.5vw,118px);letter-spacing:-.04em;line-height:.9;margin-bottom:22px}
h1.hero-h em{font-style:italic;font-weight:400;color:var(--green)}
.hero-sub{font-size:15px;color:var(--mute);line-height:1.65;max-width:440px;margin-bottom:30px}
.hero-sub b{color:var(--ink);font-weight:500}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:44px}
.hero-stats{display:flex;gap:0}
.hs{padding:18px 26px;border-left:1px solid var(--line)}
.hs:first-child{padding-left:0;border-left:none}
.hs-n{font-family:'Fraunces',serif;font-weight:500;font-size:32px;letter-spacing:-.03em;line-height:1;color:var(--ink)}
.hs-l{font-family:'Geist Mono';font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--mute);margin-top:4px}
.hero-right{position:relative;border-radius:20px;overflow:hidden;min-height:580px}
.hero-right img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.hero-right .veil{position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(17,17,16,.78) 100%)}
.hero-right .caption{position:absolute;bottom:28px;left:28px;right:28px;color:var(--paper)}
.caption-tag{font-family:'Geist Mono';font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:rgba(245,244,240,.55);margin-bottom:8px}
.caption-title{font-family:'Fraunces',serif;font-weight:500;font-size:26px;letter-spacing:-.02em;line-height:1.08}

/* FEATURES */
.feat{padding:80px 20px 60px}
.feat-head{max-width:1400px;margin:0 auto 44px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:end}
.feat-head .mono{font-family:'Geist Mono';font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--green);margin-bottom:12px}
.feat-head h2{font-family:'Fraunces',serif;font-weight:500;font-size:clamp(40px,5vw,70px);letter-spacing:-.035em;line-height:.93}
.feat-head h2 em{font-style:italic;color:var(--green);font-weight:400}
.feat-head p{font-family:'Fraunces',serif;font-style:italic;font-size:18px;line-height:1.45;color:var(--mute);max-width:420px;justify-self:end;align-self:end}

/* BENTO — explicit grid placement, no empty space */
.bento{
  max-width:1400px;
  margin:0 auto;
  display:grid;
  grid-template-columns:repeat(6,1fr);
  grid-template-rows:260px 260px 260px 260px;
  gap:12px;
}

/* Explicit positions */
.b1{ grid-column:1/4; grid-row:1/3; } /* large image left, 2 rows */
.b2{ grid-column:4/7; grid-row:1/2; } /* green card right top */
.b3{ grid-column:4/5; grid-row:2/3; } /* small dark */
.b4{ grid-column:5/6; grid-row:2/3; } /* small olive */
.b5{ grid-column:6/7; grid-row:2/3; } /* small light */
.b7{ grid-column:1/4; grid-row:3/5; } /* stat card left bottom, 2 rows */
.b6{ grid-column:4/7; grid-row:3/5; } /* large image right, 2 rows */

.b{
  border-radius:16px;overflow:hidden;position:relative;
  padding:26px;display:flex;flex-direction:column;justify-content:space-between;
}
.b .tag{font-family:'Geist Mono';font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:rgba(245,244,240,.5)}
.b h3{font-family:'Fraunces',serif;font-weight:500;font-size:22px;letter-spacing:-.02em;line-height:1.08}
.b p{font-size:13px;line-height:1.55;margin-top:7px;max-width:320px}

.b.dark{background:var(--ink);color:var(--paper)}
.b.dark p{color:rgba(245,244,240,.6)}
.b.dark .tag{color:rgba(245,244,240,.45)}

.b.green{background:var(--green);color:var(--white)}
.b.green .tag{color:rgba(255,255,255,.65)}
.b.green p{color:rgba(255,255,255,.82)}
.b.green h3{font-size:26px}

.b.olive{background:var(--olive);color:var(--paper)}
.b.olive .tag{color:rgba(245,244,240,.55)}
.b.olive p{color:rgba(245,244,240,.7)}

.b.light{background:var(--paper-2);color:var(--ink)}
.b.light .tag{color:var(--green)}
.b.light p{color:var(--mute)}

.b.moss{background:var(--moss);color:var(--paper)}
.b.moss .tag{color:rgba(245,244,240,.45)}
.b.moss p{color:rgba(245,244,240,.6)}

.b.img-card{padding:0}
.b.img-card img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.b.img-card .ov{
  position:absolute;inset:0;padding:28px;
  display:flex;flex-direction:column;justify-content:flex-end;
  background:linear-gradient(transparent 30%,rgba(17,17,16,.9));
}
.b.img-card .ov h3{color:var(--paper);font-size:28px;letter-spacing:-.025em}
.b.img-card .ov .tag{color:rgba(245,244,240,.7);margin-bottom:10px}
.b.img-card .ov p{color:rgba(245,244,240,.72);font-size:13px;line-height:1.55;margin-top:7px}

.b-big-num{
  font-family:'Fraunces',serif;font-weight:500;
  font-size:clamp(60px,7vw,100px);letter-spacing:-.04em;line-height:.85;
  color:var(--gold);
}

/* PRICING */
.price{padding:80px 20px;background:var(--ink);color:var(--paper)}
.price-head{max-width:1400px;margin:0 auto 48px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:end}
.price-head .mono{font-family:'Geist Mono';font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:12px}
.price-head h2{font-family:'Fraunces',serif;font-weight:500;font-size:clamp(40px,5vw,70px);letter-spacing:-.035em;line-height:.93;color:var(--paper)}
.price-head h2 em{font-style:italic;font-weight:400;color:var(--gold)}
.price-head p{font-family:'Fraunces',serif;font-style:italic;font-size:18px;line-height:1.5;color:rgba(245,244,240,.5);max-width:400px;justify-self:end;align-self:end}

.price-grid{max-width:1400px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.pc{background:rgba(245,244,240,.05);border-radius:18px;padding:28px;display:flex;flex-direction:column;border:1px solid rgba(245,244,240,.09);transition:border-color .25s}
.pc:hover{border-color:rgba(245,244,240,.25)}
.pc.hot{background:var(--green);border-color:var(--green)}
.pc .plan-name{font-family:'Geist Mono';font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:rgba(245,244,240,.5);margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
.pc.hot .plan-name{color:rgba(255,255,255,.7)}
.pc .badge{background:var(--gold);color:var(--moss);font-size:9px;font-weight:500;letter-spacing:.14em;text-transform:uppercase;padding:3px 9px;border-radius:999px}
.pc .amount{font-family:'Fraunces',serif;font-weight:500;font-size:44px;letter-spacing:-.04em;line-height:.9;color:var(--paper)}
.pc .period{font-family:'Geist Mono';font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:rgba(245,244,240,.4);margin:8px 0 16px}
.pc .desc{font-size:13px;color:rgba(245,244,240,.55);line-height:1.5;margin-bottom:20px;min-height:54px}
.pc ul{list-style:none;margin-bottom:24px;display:flex;flex-direction:column;gap:9px}
.pc li{font-size:13px;color:rgba(245,244,240,.72);display:flex;align-items:center;gap:10px;padding-bottom:9px;border-bottom:1px solid rgba(245,244,240,.08)}
.pc.hot li{border-bottom-color:rgba(255,255,255,.12);color:rgba(255,255,255,.88)}
.pc li::before{content:"";width:14px;height:1px;background:var(--gold);flex-shrink:0}
.pc .cta-btn{display:block;text-align:center;padding:13px;background:rgba(245,244,240,.08);font-size:13px;font-weight:500;color:var(--paper);border-radius:10px;transition:all .2s;margin-top:auto;border:1px solid rgba(245,244,240,.1)}
.pc .cta-btn:hover{background:var(--paper);color:var(--ink)}
.pc.hot .cta-btn{background:var(--gold);color:var(--moss);border-color:transparent}
.pc.hot .cta-btn:hover{background:var(--paper);color:var(--ink)}
.price-note{max-width:1400px;margin:24px auto 0;text-align:center;font-family:'Fraunces',serif;font-style:italic;font-size:15px;color:rgba(245,244,240,.38)}
.price-note b{color:var(--gold);font-style:normal;font-weight:500}

/* FOOTER */
footer{background:var(--moss);color:var(--paper);padding:56px 20px 26px}
.ft{max-width:1400px;margin:0 auto;display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;gap:40px;padding-bottom:36px;border-bottom:1px solid rgba(245,244,240,.1)}
.ft-logo{background:#fff;border-radius:7px;padding:5px 9px;display:inline-block;margin-bottom:14px}
.ft-logo img{height:24px;width:auto;object-fit:contain}
.ft-desc{font-family:'Fraunces',serif;font-style:italic;font-size:15px;line-height:1.45;color:rgba(245,244,240,.48);max-width:250px}
.ft-h{font-family:'Geist Mono';font-size:10px;font-weight:500;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:16px}
.ft-a{display:block;font-size:13px;color:rgba(245,244,240,.52);margin-bottom:9px;transition:color .2s}
.ft-a:hover{color:var(--paper)}
.ft-bottom{max-width:1400px;margin:20px auto 0;display:flex;justify-content:space-between;font-family:'Geist Mono';font-size:10px;letter-spacing:.06em;color:rgba(245,244,240,.28);text-transform:uppercase}

/* DEMO MODAL */
.demo-modal{display:none;position:fixed;inset:0;background:rgba(17,17,16,.85);z-index:200;align-items:center;justify-content:center;padding:20px}
.demo-modal.open{display:flex}
.demo-box{background:var(--ink);border-radius:20px;width:100%;max-width:800px;overflow:hidden;position:relative}
.demo-close{position:absolute;top:14px;right:14px;background:rgba(245,244,240,.1);border:none;color:var(--paper);width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;z-index:1;font-family:inherit}
.demo-placeholder{aspect-ratio:16/9;background:var(--moss);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px}
.demo-placeholder .play{width:60px;height:60px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--moss)}
.demo-placeholder p{font-family:'Fraunces',serif;font-style:italic;font-size:17px;color:rgba(245,244,240,.45)}

/* ANIMATIONS */
.fade{opacity:0;transform:translateY(18px);transition:opacity .7s,transform .7s}
.fade.in{opacity:1;transform:none}

/* RESPONSIVE */
@media(max-width:1100px){
  .bento{
    grid-template-columns:1fr 1fr 1fr;
    grid-template-rows:auto;
  }
  .b1{grid-column:1/3;grid-row:auto}
  .b2{grid-column:3/4;grid-row:auto}
  .b3,.b4,.b5{grid-column:span 1;grid-row:auto}
  .b7{grid-column:1/3;grid-row:auto}
  .b6{grid-column:3/4;grid-row:auto}
  .price-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:820px){
  nav{padding:9px 14px;left:12px;right:12px;top:10px}
  .nav-links{display:none}
  .hero{grid-template-columns:1fr;padding:90px 14px 20px;min-height:auto}
  .hero-right{min-height:300px}
  .feat,.price{padding:60px 14px}
  .feat-head,.price-head{grid-template-columns:1fr;gap:16px}
  .feat-head p,.price-head p{justify-self:start}
  .bento{grid-template-columns:1fr 1fr;grid-template-rows:auto}
  .b1,.b7{grid-column:1/3}
  .b2{grid-column:1/3}
  .b3,.b4,.b5{grid-column:span 1}
  .b6{grid-column:1/3}
  .price-grid{grid-template-columns:1fr}
  .ft{grid-template-columns:1fr 1fr;gap:26px}
  .ft-bottom{flex-direction:column;gap:6px;text-align:center}
}
@media(max-width:500px){
  .ft{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="/" class="nav-logo">
    <img src="/images/logo.png" alt="Nyumba">
  </a>
  <div class="nav-links">
    <a href="#features">Features</a>
    <a href="#pricing">Pricing</a>
  </div>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost-light">Sign in</a>
    <a href="/register/step1" class="btn btn-green">Start free <span class="ic">→</span></a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-left fade">
    <div>
      <h1 class="hero-h">Collect rent,<br>not <em>excuses</em>.</h1>
      <p class="hero-sub">
        Nyumba handles invoices, M-Pesa reconciliation, utility billing and SMS reminders for your rental portfolio.
        <b>Your job is ownership, not administration.</b>
      </p>
      <div class="hero-actions">
        <a href="/register/step1" class="btn btn-green">Start free trial <span class="ic">→</span></a>
        <button class="btn btn-outline" onclick="document.getElementById('demo-modal').classList.add('open')">
          <span class="ic" style="background:var(--green);color:#fff">▶</span> Watch demo
        </button>
      </div>
    </div>
    <div class="hero-stats">
      <div class="hs"><div class="hs-n">500+</div><div class="hs-l">Landlords</div></div>
      <div class="hs"><div class="hs-n">12k+</div><div class="hs-l">Units live</div></div>
      <div class="hs"><div class="hs-n">7 days</div><div class="hs-l">Free trial</div></div>
    </div>
  </div>

  <div class="hero-right fade">
    <img src="https://i.pinimg.com/1200x/51/2c/b1/512cb183d68c0fc6c3dd8ad876092c12.jpg" alt="Residential apartment block in Nairobi">
    <div class="veil"></div>
    <div class="caption">
      <div class="caption-tag">Nairobi, Kenya</div>
      <div class="caption-title">The operating system<br>for Kenyan landlords.</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features" class="feat">
  <div class="feat-head fade">
    <div>
      <div class="mono">What it does</div>
      <h2>One platform.<br>Zero <em>spreadsheets</em>.</h2>
    </div>
    <p>Six things Nyumba runs in the background while you get on with your life.</p>
  </div>

  <div class="bento fade">

    <!-- b1: Large image — Invoicing (cols 1-3, rows 1-2) -->
    <div class="b b1 img-card">
      <img src="https://i.pinimg.com/1200x/48/05/05/480505da4577c3030a7c90fed197332c.jpg" alt="Apartment block">
      <div class="ov">
        <div>
          <span class="tag">01 · Invoicing</span>
          <h3>Invoices that send themselves.</h3>
          <p>On the 1st of every month Nyumba builds a PDF for every tenant covering rent, water, electricity and extras, then pushes it by SMS. No manual work.</p>
        </div>
      </div>
    </div>

    <!-- b2: SMS reminders (cols 4-6, row 1) -->
    <div class="b b2 green">
      <span class="tag">02 · SMS reminders</span>
      <div>
        <h3>Three touches per tenant.<br>Zero effort from you.</h3>
        <p>Due in 3 days. Due today. Three days in arrears. Nyumba sends all three automatically and logs every delivery receipt.</p>
      </div>
    </div>

    <!-- b3: M-Pesa (col 4, row 2) -->
    <div class="b b3 dark">
      <span class="tag">03 · M-Pesa</span>
      <div>
        <div class="b-big-num">0</div>
        <p style="color:rgba(245,244,240,.5);font-size:12px;margin-top:6px">manual entries</p>
      </div>
    </div>

    <!-- b4: Utilities (col 5, row 2) -->
    <div class="b b4 olive">
      <span class="tag">04 · Utilities</span>
      <div>
        <h3 style="font-size:18px">Meter to bill.<br>No argument.</h3>
        <p>Caretaker enters the reading. Nyumba computes and bills the right tenant with proof attached.</p>
      </div>
    </div>

    <!-- b5: Reports (col 6, row 2) -->
    <div class="b b5 light">
      <span class="tag">05 · Reports</span>
      <div>
        <h3 style="font-size:18px">Know your numbers every Monday.</h3>
        <p>Collection rate, occupancy and net income in one SMS. Full PDF for the bank on demand.</p>
      </div>
    </div>

    <!-- b7: Stats card (cols 1-3, rows 3-4) — fills the empty left space -->
    <div class="b b7 moss" style="justify-content:space-between">
      <span class="tag">The numbers</span>
      <div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2px;margin-bottom:18px">
          <div style="background:rgba(245,244,240,.06);border-radius:10px;padding:18px">
            <div style="font-family:'Fraunces',serif;font-size:42px;font-weight:500;letter-spacing:-.03em;color:var(--gold);line-height:1">500+</div>
            <div style="font-family:'Geist Mono';font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:rgba(245,244,240,.45);margin-top:5px">Landlords</div>
          </div>
          <div style="background:rgba(245,244,240,.06);border-radius:10px;padding:18px">
            <div style="font-family:'Fraunces',serif;font-size:42px;font-weight:500;letter-spacing:-.03em;color:var(--gold);line-height:1">12k+</div>
            <div style="font-family:'Geist Mono';font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:rgba(245,244,240,.45);margin-top:5px">Units live</div>
          </div>
          <div style="background:rgba(245,244,240,.06);border-radius:10px;padding:18px">
            <div style="font-family:'Fraunces',serif;font-size:42px;font-weight:500;letter-spacing:-.03em;color:var(--gold);line-height:1">7</div>
            <div style="font-family:'Geist Mono';font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:rgba(245,244,240,.45);margin-top:5px">Day free trial</div>
          </div>
          <div style="background:rgba(26,107,82,.35);border-radius:10px;padding:18px;display:flex;flex-direction:column;justify-content:space-between">
            <div style="font-family:'Fraunces',serif;font-size:16px;font-style:italic;color:rgba(245,244,240,.75);line-height:1.3">Ready in under an hour.</div>
            <a href="/register/step1" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--gold);font-weight:500;margin-top:10px">Start free <span style="font-size:10px">→</span></a>
          </div>
        </div>
      </div>
    </div>

    <!-- b6: Large image — Audit (cols 4-6, rows 3-4) -->
    <div class="b b6 img-card">
      <img src="https://i.pinimg.com/736x/41/8d/53/418d53277a6707f79d69e75f9cf429d0.jpg" alt="Property manager reviewing portfolio">
      <div class="ov">
        <div>
          <span class="tag">06 · Audit trail</span>
          <h3>Every action, timestamped.</h3>
          <p>Know exactly who edited what, when, from which device. Disputes become a 30 second lookup instead of a two week argument.</p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- PRICING -->
<section id="pricing" class="price">
  <div class="price-head fade">
    <div>
      <div class="mono">Pricing</div>
      <h2>Start free. Pay when<br>it <em>pays for itself</em>.</h2>
    </div>
    <p>All plans get a 7 day free trial. No card required. The price you see is the price you pay.</p>
  </div>

  <div class="price-grid fade">
    <div class="pc">
      <div class="plan-name">Starter</div>
      <div class="amount">KES 2,000</div>
      <div class="period">per month</div>
      <p class="desc">For the new landlord who is done with notebooks and wants the basics running automatically.</p>
      <ul>
        <li>Up to 20 units</li>
        <li>50 SMS credits a month</li>
        <li>PDF invoices</li>
        <li>Utility tracking</li>
        <li>Basic reports</li>
      </ul>
      <a href="/register/step1" class="cta-btn">Get started</a>
    </div>

    <div class="pc hot">
      <div class="plan-name">Growth <span class="badge">Popular</span></div>
      <div class="amount">KES 4,500</div>
      <div class="period">per month</div>
      <p class="desc">For the growing landlord who wants the whole system running itself without daily oversight.</p>
      <ul>
        <li>Up to 50 units</li>
        <li>100 SMS credits a month</li>
        <li>Bulk invoicing</li>
        <li>Auto invoice schedule</li>
        <li>Advanced reports</li>
      </ul>
      <a href="/register/step1" class="cta-btn">Start free trial</a>
    </div>

    <div class="pc">
      <div class="plan-name">Pro</div>
      <div class="amount">KES 7,000</div>
      <div class="period">per month</div>
      <p class="desc">For the serious operator running multiple buildings with a caretaker team and co-owners.</p>
      <ul>
        <li>Up to 100 units</li>
        <li>200 SMS credits a month</li>
        <li>Multi-user access</li>
        <li>Full audit trail</li>
        <li>Priority support</li>
      </ul>
      <a href="/register/step1" class="cta-btn">Get started</a>
    </div>

    <div class="pc">
      <div class="plan-name">Enterprise</div>
      <div class="amount" style="font-size:34px;padding-top:8px">Custom</div>
      <div class="period">tailored to your portfolio</div>
      <p class="desc">For property managers running 100 plus units across multiple owners and buildings.</p>
      <ul>
        <li>Unlimited units</li>
        <li>Custom SMS bundle</li>
        <li>Dedicated support</li>
        <li>API access</li>
        <li>99.9% uptime SLA</li>
      </ul>
      <a href="mailto:hello@nyumba.co.ke" class="cta-btn">Talk to us</a>
    </div>
  </div>

  <p class="price-note">Pay 6 months, get 1 free. <b>Pay 12 months, get 2 free.</b> Extra SMS at KES 1 per credit.</p>
</section>

<!-- FOOTER -->
<footer>
  <div class="ft">
    <div>
      <a href="/" class="ft-logo"><img src="/images/logo.png" alt="Nyumba"></a>
      <p class="ft-desc">Kenya's most complete rental management platform. Built for landlords who mean business.</p>
    </div>
    <div>
      <div class="ft-h">Product</div>
      <a href="#features" class="ft-a">Features</a>
      <a href="#pricing" class="ft-a">Pricing</a>
      <a href="/register/step1" class="ft-a">Sign up</a>
      <a href="/login" class="ft-a">Sign in</a>
    </div>
    <div>
      <div class="ft-h">Legal</div>
      <a href="#" class="ft-a">Privacy policy</a>
      <a href="#" class="ft-a">Terms of service</a>
    </div>
    <div>
      <div class="ft-h">Talk to us</div>
      <a href="mailto:hello@nyumba.co.ke" class="ft-a">hello@nyumba.co.ke</a>
      <a href="tel:0700000000" class="ft-a">0700 000 000</a>
      <span class="ft-a">Nairobi, Kenya</span>
    </div>
  </div>
  <div class="ft-bottom">
    <span>2026 Nyumba. All rights reserved.</span>
    <span>Built in Nairobi, for landlords everywhere.</span>
  </div>
</footer>

<!-- DEMO MODAL -->
<div class="demo-modal" id="demo-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="demo-box">
    <button class="demo-close" onclick="document.getElementById('demo-modal').classList.remove('open')">&times;</button>
    <div class="demo-placeholder">
      <div class="play">▶</div>
      <p>Demo video coming soon.</p>
    </div>
  </div>
</div>

<script>
const io = new IntersectionObserver(
  es => es.forEach(e => e.isIntersecting && e.target.classList.add('in')),
  {threshold:.1}
);
document.querySelectorAll('.fade').forEach(el => io.observe(el));
document.addEventListener('keydown', e => {
  if(e.key === 'Escape') document.getElementById('demo-modal').classList.remove('open');
});
</script>
</body>
</html>
