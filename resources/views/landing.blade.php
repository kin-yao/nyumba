<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.consent-mode')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-P5FWPX45');</script>
<!-- End Google Tag Manager -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NyumbaPc | Rent Collection Software for Property Agents & Landlords</title>
<meta name="description" content="NyumbaPc is rent collection software for landlords and property managers in Kenya. Send SMS rent reminders, match M-Pesa payments to the right unit, and see who has paid. Free for 7 days.">
<meta property="og:title" content="NyumbaPc | Rent Collection Software for Kenyan Landlords">
<meta property="og:description" content="Send rent reminders by SMS, match M-Pesa payments to the right unit, and know who has paid. Built in Kenya for Kenyan rentals.">
<meta property="og:image" content="https://NyumbaPc.co.ke/dashboard-preview.png">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0e3f30">
<link rel="canonical" href="https://NyumbaPc.co.ke">
<link rel="preconnect" href="https://api.fontshare.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap" rel="stylesheet">
<style>
/* One typeface. Three colours. No gradients, no shadows, no rounded cards. */
:root{
  --ink:#14110f;
  --green:#0e3f30;
  --gold:#c2924f;
  --line:#e3e1da;
  --line-d:rgba(255,255,255,.18);
  --mute:#6f6a62;
  --mute-d:rgba(255,255,255,.55);
  --red:#a8402f;
  --pad:clamp(20px,5vw,72px);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{font-family:'Satoshi',system-ui,sans-serif;background:#fff;color:var(--ink);font-size:16px;line-height:1.5;-webkit-font-smoothing:antialiased;overflow-x:hidden}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
button{font-family:inherit;color:inherit}
:focus-visible{outline:2px solid var(--ink);outline-offset:2px}
::selection{background:var(--ink);color:#fff}
.mono{font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace}
.hand{font-family:'Caveat',cursive}
/* Type scale: big, tight, few words. */
.xl{font-weight:900;font-size:clamp(52px,10.5vw,168px);line-height:.88;letter-spacing:-.05em}
.lg{font-weight:900;font-size:clamp(34px,6vw,78px);line-height:.92;letter-spacing:-.045em}
.md{font-weight:700;font-size:clamp(22px,3vw,34px);line-height:1.1;letter-spacing:-.03em}
.sm{font-size:15px;color:var(--mute);line-height:1.55;max-width:34ch}
.on-dark .sm{color:var(--mute-d)}
.tiny{font-size:12px;color:var(--mute);letter-spacing:.01em}
.on-dark .tiny{color:var(--mute-d)}
.pad{padding-left:var(--pad);padding-right:var(--pad)}
.sec{padding:clamp(64px,9vw,132px) var(--pad)}
.rule{border-top:1px solid var(--line)}
.on-dark{background:var(--ink);color:#fff}
.on-dark .rule,.on-green .rule{border-top-color:var(--line-d)}
.on-green{background:var(--green);color:#fff}
/* Buttons: flat, square, two states. */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:17px 30px;border:1px solid var(--ink);background:var(--ink);color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s,color .15s,border-color .15s}
.btn:hover{background:#fff;color:var(--ink)}
.btn.alt{background:transparent;color:var(--ink)}
.btn.alt:hover{background:var(--ink);color:#fff}
.btn.gold{background:var(--gold);border-color:var(--gold);color:var(--ink)}
.btn.gold:hover{background:var(--ink);border-color:var(--ink);color:#fff}
.btn.inv{background:#fff;border-color:#fff;color:var(--ink)}
.btn.inv:hover{background:transparent;color:#fff}
.btn.inv-alt{background:transparent;border-color:var(--line-d);color:#fff}
.btn.inv-alt:hover{background:#fff;border-color:#fff;color:var(--ink)}
.btn.s{padding:11px 20px;font-size:13px}
.btn[disabled]{opacity:.35;pointer-events:none}
/* Nav */
.nav{position:sticky;top:0;z-index:60;background:#fff;border-bottom:1px solid var(--line);height:72px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.nav img{height:38px;width:auto}
.nav-l{display:flex;gap:28px;align-items:center}
.nav-l a{font-size:14px;font-weight:500}
.nav-l a:hover{text-decoration:underline;text-underline-offset:4px}
.burger{display:none;background:none;border:none;cursor:pointer;padding:8px;flex-direction:column;gap:5px}
.burger span{width:22px;height:2px;background:var(--ink);display:block;transition:transform .2s,opacity .2s}
.burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.burger.open span:nth-child(2){opacity:0}
.burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
/* Hero: two photographs crossfading behind the headline. */
.hero{position:relative;overflow:hidden;background:var(--ink);color:#fff;padding:clamp(96px,14vw,190px) var(--pad) clamp(64px,9vw,110px)}
.hero-bg{position:absolute;inset:0;z-index:0}
.hero-bg figure{position:absolute;inset:0;background-color:var(--green);background-size:cover;background-position:center;opacity:0;animation:xfade 18s infinite;will-change:opacity,transform}
.hero-bg figure:nth-child(2){animation-delay:9s}
@keyframes xfade{
  0%{opacity:0;transform:scale(1.08)}
  6%{opacity:1}
  44%{opacity:1}
  50%{opacity:0;transform:scale(1.01)}
  100%{opacity:0;transform:scale(1.01)}
}
.hero-veil{position:absolute;inset:0;z-index:1;background:rgba(20,17,15,.64)}
.hero>*:not(.hero-bg):not(.hero-veil){position:relative;z-index:2}
.hero h1{max-width:11ch}
.hero .after{font-weight:500;font-size:clamp(18px,2.4vw,30px);letter-spacing:-.02em;margin-top:clamp(20px,3vw,34px);color:var(--gold);max-width:24ch}
.hero-row{display:flex;gap:14px;flex-wrap:wrap;margin-top:36px;align-items:center}
.hero-meta{display:flex;gap:26px;flex-wrap:wrap;margin-top:34px;font-size:12.5px;color:var(--mute-d)}
.hero-meta b{color:#fff;font-weight:700}
/* Full bleed photographic bands */
.band{position:relative;overflow:hidden;min-height:clamp(240px,32vw,440px);display:flex;align-items:flex-end;color:#fff}
.band-img{position:absolute;inset:0;background-color:var(--green);background-size:cover;background-position:center;animation:burns 26s ease-in-out infinite alternate;will-change:transform}
@keyframes burns{from{transform:scale(1)}to{transform:scale(1.1)}}
.band-veil{position:absolute;inset:0;background:rgba(20,17,15,.5)}
.band-in{position:relative;z-index:2;padding:var(--pad);width:100%}
.band-in .md{max-width:18ch}
.band.tall{min-height:clamp(280px,40vw,540px)}
/* Framed photograph, square edges */
.frm{position:relative;overflow:hidden;border:1px solid var(--line);background:var(--green)}
.frm img{width:100%;height:100%;object-fit:cover;transition:transform .8s cubic-bezier(.2,.7,.2,1)}
.frm:hover img{transform:scale(1.05)}
.frm .cap{position:absolute;left:0;bottom:0;background:var(--ink);color:#fff;font-size:11.5px;padding:9px 15px}
/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr)}
.stat{padding:clamp(28px,4vw,48px) 0;border-left:1px solid var(--line);padding-left:clamp(18px,2.5vw,32px)}
.stat:first-child{border-left:none;padding-left:0}
.stat .n{font-weight:900;font-size:clamp(28px,4vw,52px);letter-spacing:-.045em;line-height:1}
.stat .l{font-size:12.5px;color:var(--mute);margin-top:8px}
/* Section heading block */
.head{display:grid;grid-template-columns:1.25fr 1fr;gap:clamp(20px,4vw,60px);align-items:end;margin-bottom:clamp(34px,5vw,60px)}
.head .sm{justify-self:end;text-align:left}
/* Two-panel compare: the book, and the real product screen */
.two{display:grid;grid-template-columns:.85fr 1.35fr;gap:1px;background:var(--line);border:1px solid var(--line)}
.two>div{background:#fff;min-width:0}
.panel-h{padding:18px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:baseline;gap:12px}
.panel-h .t{font-size:13px;font-weight:700}
.panel-h .s{font-size:12px;color:var(--mute)}
/* The counter book */
.book{background:#fdfcf8;position:relative}
.book .panel-h{border-bottom-color:#d8cfc0}
.brow{display:grid;grid-template-columns:52px 1fr auto;align-items:center;height:46px;border-bottom:1px solid #dfe6ec;font-family:'Caveat',cursive;font-size:20px;color:#2c3a48}
.brow .no{text-align:center;font-size:15px;color:#a9a296}
.brow .nm{padding-left:16px}
.brow .am{padding-right:22px;font-weight:600}
.brow.out .nm,.brow.out .am{text-decoration:line-through;text-decoration-color:#cf9187;color:#8d8f95}
.brow .q{color:#cf9187}
.book-f{padding:16px 22px;font-family:'Caveat',cursive;font-size:19px;color:#cf9187}
.book-photo{position:relative;border-top:1px solid #d8cfc0;background:var(--green);overflow:hidden}
.book-photo img{width:100%;height:clamp(150px,17vw,215px);object-fit:cover;object-position:center 35%;display:block}
.book-photo .cap{position:absolute;left:0;bottom:0;background:var(--ink);color:#fff;font-size:11.5px;padding:8px 14px}
/* The real screenshot beside it */
.shotpane img{width:100%;display:block}
.shotpane .cap{padding:14px 22px;font-size:12.5px;color:var(--mute);border-top:1px solid var(--line)}
/* Sandbox */
.sand{display:grid;grid-template-columns:1fr 340px;gap:clamp(20px,3vw,40px);align-items:start}
.sb{border:1px solid var(--line)}
.sb-h{padding:20px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}
.sb-h .t{font-size:14px;font-weight:700}
.sb-h .s{font-size:12.5px;color:var(--mute);margin-top:3px}
.sb-t{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;padding:18px 22px;border-bottom:1px solid var(--line);transition:background .3s}
.sb-t.hit{background:#f1f6f3}
.sb-t .nm{font-size:14.5px;font-weight:500}
.sb-t .mt{font-size:12px;color:var(--mute);margin-top:3px}
.sb-t .bal{font-weight:900;font-size:17px;letter-spacing:-.03em;text-align:right}
.sb-t .bal.zero{color:var(--green)}
.sb-t .bal .st{display:block;font-size:11px;font-weight:500;color:var(--mute);margin-top:4px;letter-spacing:0}
.sb-a{padding:18px 22px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;border-bottom:1px solid var(--line)}
.sb-a .h{font-size:12.5px;color:var(--mute);margin-left:auto}
.sb-log{padding:0 22px;max-height:0;overflow:hidden;transition:max-height .35s ease}
.sb-log.on{max-height:340px;padding-top:4px;padding-bottom:16px}
.sb-log .e{font-size:12.5px;color:var(--mute);padding:10px 0;border-bottom:1px solid var(--line);line-height:1.5}
.sb-log .e:last-child{border-bottom:none}
.sb-log .e b{color:var(--ink)}
/* Phone. Drawn in CSS so the demo reads as a real handset rather than a panel.
   This is the only rounded thing on the page, because a phone is round. */
.ph{position:relative;width:318px;max-width:100%;margin:0 auto}
.ph-body{position:relative;background:#111;border-radius:46px;padding:11px}
.ph-btn{position:absolute;width:3px;background:#2b2b2b}
.ph-btn.v1{left:-2px;top:118px;height:30px}
.ph-btn.v2{left:-2px;top:158px;height:30px}
.ph-btn.pw{right:-2px;top:142px;height:52px}
.ph-scr{position:relative;background:#fff;border-radius:36px;overflow:hidden;aspect-ratio:9/19;display:flex;flex-direction:column}
.ph-island{position:absolute;top:9px;left:50%;transform:translateX(-50%);width:88px;height:24px;background:#000;border-radius:14px;z-index:6}
.ph-status{display:flex;justify-content:space-between;align-items:center;padding:13px 20px 8px;font-size:11.5px;font-weight:700;color:#111;flex-shrink:0}
.ph-status .sig{display:flex;align-items:center;gap:4px}
.ph-status .bat{width:22px;height:11px;border:1px solid #111;border-radius:3px;position:relative;padding:1.5px}
.ph-status .bat i{display:block;height:100%;width:72%;background:#111;border-radius:1px}
.ph-status .bat::after{content:"";position:absolute;right:-3px;top:3.5px;width:2px;height:4px;background:#111;border-radius:0 1px 1px 0}
.ph-top{display:flex;align-items:center;gap:9px;padding:7px 13px 10px;border-bottom:1px solid #e6e6ea;flex-shrink:0}
.ph-top .bk{color:#0e3f30;flex-shrink:0}
.ph-top .av{width:27px;height:27px;border-radius:50%;background:#0e3f30;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:700;flex-shrink:0}
.ph-top .nm{font-size:12.5px;font-weight:700;line-height:1.2;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ph-top .nm span{display:block;font-size:9px;font-weight:400;color:#8a8a8e}
.ph-top .inf{margin-left:auto;color:#0e3f30;flex-shrink:0}
.ph-m{flex:1;min-height:0;padding:12px 11px 8px;display:flex;flex-direction:column;gap:5px;overflow-y:auto;background:#fff}
.ph-day{align-self:center;font-size:9px;color:#9a9aa0;padding:5px 0 7px;font-weight:500}
.bub{max-width:84%;align-self:flex-start;background:#e9e9eb;color:#111;padding:8px 12px;border-radius:17px 17px 17px 5px;font-size:12.2px;line-height:1.4;animation:pop .28s cubic-bezier(.2,.9,.3,1) both}
.bub.me{align-self:flex-end;background:#0e3f30;color:#fff;border-radius:17px 17px 5px 17px}
.bub .who{display:block;font-size:8.5px;font-weight:700;letter-spacing:.07em;color:#7d7d84;margin-bottom:4px}
.bub.me .who{color:rgba(255,255,255,.6)}
.bub .code{color:#8a6222;font-weight:600}
.bub.me .code{color:#e8c48a}
.bub b{font-weight:700}
.bub .tm{display:block;font-size:8.5px;color:#9a9aa0;margin-top:5px}
.bub.me .tm{color:rgba(255,255,255,.55);text-align:right}
.typing{align-self:flex-start;background:#e9e9eb;padding:11px 14px;border-radius:17px 17px 17px 5px;display:flex;gap:4px;animation:pop .2s ease both}
.typing i{width:6px;height:6px;border-radius:50%;background:#a3a3a9;display:block;animation:dot 1.15s infinite}
.typing i:nth-child(2){animation-delay:.16s}
.typing i:nth-child(3){animation-delay:.32s}
@keyframes dot{0%,58%,100%{opacity:.32;transform:translateY(0)}28%{opacity:1;transform:translateY(-3px)}}
@keyframes pop{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:none}}
.ph-e{margin:auto;text-align:center;color:#9a9aa0;font-size:10.5px;line-height:1.55;padding:16px}
.ph-in{display:flex;align-items:center;gap:7px;padding:7px 11px;border-top:1px solid #e6e6ea;flex-shrink:0}
.ph-in .fake{flex:1;border:1px solid #d9d9de;border-radius:15px;padding:6px 11px;font-size:10.5px;color:#a3a3a9}
.ph-in .snd{width:25px;height:25px;border-radius:50%;background:#0e3f30;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ph-home{height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ph-home::after{content:"";width:104px;height:4px;border-radius:3px;background:#111}
@keyframes up{from{opacity:0;transform:translateY(9px)}to{opacity:1;transform:none}}
/* Frame switcher */
.frame{display:grid;grid-template-columns:minmax(240px,.8fr) 1.2fr;gap:1px;background:var(--line-d);border:1px solid var(--line-d)}
.frame>div{background:var(--ink)}
.frame .price{padding:clamp(28px,4vw,48px);display:flex;flex-direction:column;justify-content:center}
.frame .price .n{font-weight:900;font-size:clamp(38px,5vw,64px);letter-spacing:-.05em;line-height:1}
.frame .right{padding:clamp(24px,3vw,40px)}
.tabs{display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid var(--line-d);margin-bottom:28px}
.tab{background:none;border:none;border-bottom:2px solid transparent;padding:0 20px 14px 0;margin-right:22px;font-size:13px;font-weight:500;color:var(--mute-d);cursor:pointer;transition:color .15s,border-color .15s}
.tab:hover{color:#fff}
.tab[aria-selected="true"]{color:#fff;border-bottom-color:var(--gold)}
.frame-out{min-height:118px;display:flex;flex-direction:column;justify-content:center}
.frame-out .q{font-weight:700;font-size:clamp(19px,2.4vw,29px);letter-spacing:-.03em;line-height:1.18;max-width:22ch;animation:up .28s ease both}
/* Audit */
.aud{border:1px solid var(--line-d);max-width:820px}
.aud-s{display:flex;border-bottom:1px solid var(--line-d);font-size:12px;color:var(--mute-d)}
.aud-s>div{flex:1;padding:14px 18px;border-right:1px solid var(--line-d)}
.aud-s>div:last-child{border-right:none}
.aud-s>div.on{color:#fff;font-weight:700}
.aud-s>div.done{color:var(--gold)}
.aud-p{padding:clamp(26px,3.5vw,42px)}
.aud-q{font-weight:700;font-size:clamp(20px,2.6vw,30px);letter-spacing:-.03em;line-height:1.15;max-width:20ch}
.aud-in{margin-top:26px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.aud-in input{width:180px;height:62px;border:1px solid var(--line-d);background:transparent;color:#fff;font-family:inherit;font-weight:900;font-size:27px;letter-spacing:-.03em;text-align:center;outline:none}
.aud-in input:focus{border-color:var(--gold)}
.aud-in .u{font-size:13.5px;color:var(--mute-d)}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}
.chip{background:none;border:1px solid var(--line-d);color:var(--mute-d);padding:8px 14px;font-size:12.5px;cursor:pointer;transition:color .15s,border-color .15s}
.chip:hover{color:#fff;border-color:#fff}
.aud-n{margin-top:30px;display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.back{background:none;border:none;color:var(--mute-d);font-size:13px;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
.back:hover{color:#fff}
.aud-big{font-weight:900;font-size:clamp(44px,8vw,104px);letter-spacing:-.055em;line-height:.9;color:var(--gold)}
.aud-lines{margin-top:30px;border-top:1px solid var(--line-d)}
.aud-line{display:flex;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid var(--line-d);font-size:13.5px;color:var(--mute-d)}
.aud-line b{color:#fff;font-weight:700;white-space:nowrap}
.aud-v{margin-top:26px;font-size:15px;line-height:1.6;max-width:52ch;color:rgba(255,255,255,.85)}
.aud-v b{color:var(--gold)}
/* Plain feature list, no cards */
.split{display:grid;grid-template-columns:360px 1fr;gap:clamp(20px,4vw,64px);align-items:start}
.list{display:grid;grid-template-columns:1fr 1fr;gap:0 clamp(24px,5vw,80px)}
.li{padding:22px 0;border-bottom:1px solid var(--line)}
.li h4{font-size:15px;font-weight:700;margin-bottom:5px}
.li p{font-size:13.5px;color:var(--mute);line-height:1.55}
/* Pricing */
.plans{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--line);border:1px solid var(--line)}
.plan{background:#fff;padding:clamp(26px,3vw,38px);display:flex;flex-direction:column}
.plan.pick{background:var(--green);color:#fff}
.plan .pn{font-size:12.5px;color:var(--mute);display:flex;justify-content:space-between;gap:10px;align-items:center}
.plan.pick .pn{color:rgba(255,255,255,.7)}
.plan .pk{border:1px solid var(--gold);color:var(--gold);font-size:10.5px;font-weight:700;padding:3px 8px}
.plan .amt{font-weight:900;font-size:clamp(32px,3.6vw,46px);letter-spacing:-.05em;line-height:1;margin-top:18px}
.plan .per{font-size:12.5px;color:var(--mute);margin-top:10px}
.plan.pick .per{color:rgba(255,255,255,.7)}
.plan .cmp{font-size:12.5px;color:var(--gold);margin-top:8px;font-weight:500}
.plan ul{list-style:none;margin:24px 0}
.plan li{font-size:13.5px;padding:9px 0;border-bottom:1px solid var(--line)}
.plan.pick li{border-bottom-color:var(--line-d)}
.plan .go{margin-top:auto;padding-top:8px}
.plan .go a{display:block;text-align:center;padding:14px;border:1px solid var(--ink);font-size:13.5px;font-weight:700;transition:background .15s,color .15s}
.plan .go a:hover{background:var(--ink);color:#fff}
.plan.pick .go a{border-color:#fff}
.plan.pick .go a:hover{background:#fff;color:var(--green)}
.calc{margin-top:1px;border:1px solid var(--line);border-top:none;padding:24px clamp(20px,3vw,32px);display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap}
.calc label{font-size:13.5px}
.calc input{width:96px;height:44px;border:1px solid var(--line);background:#fff;text-align:center;font-family:inherit;font-size:16px;font-weight:700;outline:none}
.calc input:focus{border-color:var(--ink)}
.calc .out{text-align:right;font-size:12.5px;color:var(--mute)}
.calc .out b{display:block;font-weight:900;font-size:24px;letter-spacing:-.04em;color:var(--ink);margin-bottom:3px}
/* FAQ */
.q{border-bottom:1px solid var(--line)}
.q:first-child{border-top:1px solid var(--line)}
.q summary{padding:22px 40px 22px 0;font-size:16px;font-weight:500;cursor:pointer;list-style:none;position:relative}
.q summary::-webkit-details-marker{display:none}
.q summary::after{content:"+";position:absolute;right:4px;top:50%;transform:translateY(-50%);font-size:22px;font-weight:400;transition:transform .2s}
.q[open] summary::after{transform:translateY(-50%) rotate(45deg)}
.q .a{padding:0 40px 24px 0;font-size:14.5px;color:var(--mute);line-height:1.65;max-width:62ch}
/* Footer */
.foot{padding:clamp(48px,6vw,80px) var(--pad) 28px}
.foot-g{display:grid;grid-template-columns:2fr 1fr 1fr 1.3fr;gap:36px;padding-bottom:36px;border-bottom:1px solid var(--line-d)}
.foot img{height:32px;width:auto;margin-bottom:14px}
.foot .fh{font-size:12px;color:var(--gold);font-weight:700;margin-bottom:16px}
.foot a.fa{display:block;font-size:13.5px;color:var(--mute-d);margin-bottom:10px}
.foot a.fa:hover{color:#fff}
.foot .fb{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-top:20px;font-size:12px;color:rgba(255,255,255,.35)}
@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none !important;transition-duration:.001ms !important}
  html{scroll-behavior:auto}
  .hero-bg figure:nth-child(1){opacity:1}
}
@media(max-width:1000px){
  .sand{grid-template-columns:1fr}
  .ph{width:300px}
  .head{grid-template-columns:1fr;gap:20px;align-items:start}
  .head .sm{justify-self:start}
  .frame{grid-template-columns:1fr}
  .split{grid-template-columns:1fr}
  .split .frm{max-width:440px;aspect-ratio:4/3}
  .list{grid-template-columns:1fr;gap:0}
  .stats{grid-template-columns:1fr 1fr}
  .stat:nth-child(3){border-left:none;padding-left:0}
  .stat:nth-child(-n+2){border-bottom:1px solid var(--line)}
}
@media(max-width:820px){
  .two{grid-template-columns:1fr}
  .plans{grid-template-columns:1fr}
  .foot-g{grid-template-columns:1fr 1fr}
}
@media(max-width:720px){
  .burger{display:flex}
  .nav-l{display:none;position:absolute;top:72px;left:0;right:0;flex-direction:column;gap:0;background:#fff;border-bottom:1px solid var(--line);padding:0 var(--pad) 18px;z-index:9}
  .nav-l.open{display:flex}
  .nav-l a{padding:16px 0;border-bottom:1px solid var(--line);font-size:16px;width:100%}
  .nav .btn{display:none}
  .aud-s>div{padding:12px 10px;font-size:11px}
  .aud-s .lbl{display:none}
}
@media(max-width:520px){
  .stats{grid-template-columns:1fr}
  .stat{border-left:none;padding-left:0;border-bottom:1px solid var(--line)}
  .foot-g{grid-template-columns:1fr}
  .sb-a .h{display:none}
}
</style>
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P5FWPX45" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@php
  /* ─────────────────────────────────────────────────────────────────────
     EVERY IMAGE ON THIS PAGE IS SET HERE AND NOWHERE ELSE.

     To change one: drop your new file into public/images/landing/ using the
     filename already listed below. That is the whole job. The version stamp
     is regenerated from the file's modified time on every request, so a
     replaced file can never be served from a stale browser cache again.

     If a local file is missing, the second argument is used instead, so the
     page never breaks while you are still swapping things over.
     ───────────────────────────────────────────────────────────────────── */
  $v = function (string $local, ?string $fallback = null): string {
      $abs = public_path(ltrim($local, '/'));
      if (is_file($abs)) {
          return $local . '?v=' . filemtime($abs);   // cache buster
      }
      return $fallback ?? $local;
  };

  $img = [
    // slot          your file in public/images/landing/     used if that file is absent
    'hero1'     => $v('/images/landing/hero1.jpg',     'https://i.pinimg.com/1200x/85/d7/77/85d7773783fe2535ea383937694d85d4.jpg'),
    'hero2'     => $v('/images/landing/hero2.jpg',     'https://i.pinimg.com/1200x/f8/e6/c2/f8e6c2d14f41590415274d7e9711b82f.jpg'),
    'phone'     => $v('/images/landing/phone.jpg',     'https://i.pinimg.com/736x/c8/23/3a/c8233ab4fb0966020b95b32e24ecfab6.jpg'),
    'band1'     => $v('/images/landing/band1.jpg',     'https://i.pinimg.com/1200x/61/72/dd/6172dd8b6ed05daf247ed361f5f8ce5c.jpg'),
    'cost'      => $v('/images/landing/cost.jpg',      'https://i.pinimg.com/1200x/48/05/05/480505da4577c3030a7c90fed197332c.jpg'),
    'band2'     => $v('/images/landing/band2.jpg',     'https://i.pinimg.com/1200x/63/3e/f4/633ef43b7a53cc64cc7308c1808906aa.jpg'),
    'detail'    => $v('/images/landing/detail.jpg',    'https://i.pinimg.com/736x/f9/6e/e1/f96ee1e184238cd4050fbec2cf98439a.jpg'),
    'cta'       => $v('/images/landing/cta.jpg',       'https://i.pinimg.com/736x/5e/bf/70/5ebf700934af27117d9b9ce1e3e165a5.jpg'),
   
    // The product screenshot beside the counter book. Drop a fresh capture in
    // as dashboard.png and it replaces the old one on the next page load.
    'dashboard' => $v('/images/landing/dashboard.png', '/dashboard-preview.png'),

    // The logo, versioned for the same reason.
    'logo'      => $v('/images/logo.png'),
  ];

  /* Caption under the screenshot. Change the words here. */
  $dashboardCaption = 'Collected, outstanding, and who to follow up. It updates as payments come in.';

  $tz    = new DateTimeZone('Africa/Nairobi');
  $now   = new DateTime('now', $tz);
  $dayNo = (int) $now->format('j');
  $today = (clone $now)->setTime(0, 0);
  $due   = new DateTime($now->format('Y-m-05'), $tz);
  if ($dayNo > 5) { $due->modify('first day of next month')->modify('+4 days'); }
  $d = (int) $today->diff($due)->format('%a');
  $dueLabel = $d === 0 ? 'Rent due today' : ($d === 1 ? 'Rent due tomorrow' : 'Rent due in ' . $d . ' days');
@endphp
<nav class="nav pad">
  <a href="/"><img src="{{ $img['logo'] }}" alt="NyumbaPc"></a>
  <div class="nav-l" id="navL">
    <a href="#book">How it works</a>
    <a href="#try">Try it</a>
    <a href="#cost">Cost</a>
    <a href="#pricing">Pricing</a>
    <a href="{{ route('portal.login') }}">Tenant login</a>
    <a href="{{ route('login') }}">Sign in</a>
  </div>
  <a href="{{ route('register.step1') }}" class="btn s">Start free trial</a>
  <button class="burger" id="burger" aria-label="Menu" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>
<!-- LAW 18 · The first five seconds. Recognition, not a value proposition. -->
<header class="hero">
  <div class="hero-bg" aria-hidden="true">
    <figure style="background-image:url('{{ $img['hero1'] }}')"></figure>
    <figure style="background-image:url('{{ $img['hero2'] }}')"></figure>
  </div>
  <div class="hero-veil" aria-hidden="true"></div>
  <h1 class="xl">Rent Collection Made Easier.</h1>
  <p class="after">NyumbaPc is Rental Property Management Software for landlords, Property Managers & Agents.</p>
  <p class="sm" style="color:rgba(255,255,255,.74);max-width:48ch;margin-top:20px">It sends the SMS reminders for you, matches every M-Pesa payment to the right unit, and shows you who has paid and who has not. You stop making phone calls.</p>
  <div class="hero-row">
    <a href="{{ route('register.step1') }}" class="btn gold">Start free trial</a>
    <a href="#try" class="btn inv-alt">Try it, no signup</a>
  </div>
  <div class="hero-meta">
    <span>{{ $dueLabel }}</span>
    <span><b>3,200+</b> units live</span>
    <span><b>17</b> counties</span>
    <span>7 days free, no card</span>
  </div>
</header>
<section class="pad">
  <div class="stats">
    <div class="stat"><div class="n">0</div><div class="l">Reminders you send by hand</div></div>
    <div class="stat"><div class="n">3</div><div class="l">Minutes to see who owes what</div></div>
    <div class="stat"><div class="n">5th</div><div class="l">You know by the 5th, not the 15th</div></div>
    <div class="stat"><div class="n">7</div><div class="l">Days free. No card needed.</div></div>
  </div>
</section>
<section class="band">
  <div class="band-img" style="background-image:url('{{ $img['band1'] }}')"></div>
  <div class="band-veil"></div>
  <div class="band-in"><h2 class="md">All your units. All your balances. One page.</h2></div>
</section>
<!-- LAW 16 · Goldilocks. Anchor the new thing to the object they already trust. -->
<section class="sec" id="book">
  <div class="head">
    <h2 class="lg">Your book cannot<br>send a reminder.</h2>
    <p class="sm">Keep the counter book if you like it. NyumbaPc handles the parts it cannot do: sending the invoices, reminding the tenants, and matching each payment to the right unit.</p>
  </div>
  <div class="two">
    <div class="book">
      <div class="panel-h"><span class="t hand" style="font-size:20px">Kodi &middot; Mwezi wa Tisa</span><span class="s">The book</span></div>
      <div class="brow"><span class="no">A1</span><span class="nm">Wanjiru K.</span><span class="am">12,000 &#10003;</span></div>
      <div class="brow out"><span class="no">A2</span><span class="nm">Otieno, alisema kesho</span><span class="am">12,000</span></div>
      <div class="brow"><span class="no">A3</span><span class="nm">Mueni <span class="q">(half? 6,000?)</span></span><span class="am">6,000</span></div>
      <div class="brow"><span class="no">B1</span><span class="nm">Hassan A.</span><span class="am">12,000 &#10003;</span></div>
      <div class="brow"><span class="no">B2</span><span class="nm">Chebet <span class="q">(nani alilipa?)</span></span><span class="am">?</span></div>
      <div class="brow out"><span class="no">B3</span><span class="nm">Njoroge, water?</span><span class="am">12,800</span></div>
      <div class="book-f">Balance b/f&hellip; nitahesabu jioni</div>
      
    </div>
    <div class="shotpane">
      <div class="panel-h"><span class="t">NyumbaPc</span><span class="s">The same month, on screen</span></div>
      <img src="{{ $img['dashboard'] }}" alt="The NyumbaPc dashboard: collection status, net profit, occupancy, recent payments and outstanding balances" loading="lazy">
      <div class="cap">{{ $dashboardCaption }}</div>
    </div>
  </div>
  <p class="sm" style="margin-top:26px">Your caretaker can keep writing in his book. It just stops being the only record you have.</p>
</section>
<!-- LAW 17 · Let them try. No email wall, no signup. -->
<section class="sec rule" id="try">
  <div class="head">
    <h2 class="lg">Try it here.<br>No sign up.</h2>
    <p class="sm">Send a reminder to these three tenants, then record a payment. This is the real thing running on the page. Nothing is saved and we do not ask for your email.</p>
  </div>
  <div class="sand">
    <div class="sb">
      <div class="sb-h">
        <div><div class="t">Riverside Court &middot; September</div><div class="s" id="sum">3 tenants &middot; KES 33,000 outstanding</div></div>
        <button class="btn alt s" id="reset">Reset</button>
      </div>
      <div id="tenants"></div>
      <div class="sb-a">
        <button class="btn s" id="remind">Send reminders</button>
        <button class="btn alt s" id="pay">Record M-Pesa payment</button>
        <span class="h" id="hint">Start with the reminders</span>
      </div>
      <div class="sb-log" id="log"></div>
    </div>
    <div>
      <div class="ph">
        <div class="ph-body">
          <span class="ph-btn v1"></span><span class="ph-btn v2"></span><span class="ph-btn pw"></span>
          <div class="ph-scr">
            <div class="ph-island"></div>
            <div class="ph-status">
              <span id="phClock">08:12</span>
              <span class="sig">
                <svg width="15" height="11" viewBox="0 0 17 12" fill="currentColor" aria-hidden="true"><rect x="0" y="8" width="3" height="4" rx="1"/><rect x="4.5" y="5.5" width="3" height="6.5" rx="1"/><rect x="9" y="3" width="3" height="9" rx="1"/><rect x="13.5" y="0" width="3" height="12" rx="1"/></svg>
                <svg width="14" height="11" viewBox="0 0 16 12" fill="currentColor" aria-hidden="true"><path d="M8 11.2l2-2.4a3 3 0 00-4 0zM8 6.2a6 6 0 014 1.5l1.4-1.7a8 8 0 00-10.8 0L4 7.7A6 6 0 018 6.2zM8 1.8a10 10 0 016.9 2.7l1.3-1.6a12 12 0 00-16.4 0l1.3 1.6A10 10 0 018 1.8z"/></svg>
                <span class="bat"><i></i></span>
              </span>
            </div>
            <div class="ph-top">
              <svg class="bk" width="9" height="15" viewBox="0 0 10 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 1L2 8l6 7"/></svg>
              <span class="av" id="phAv">?</span>
              <div class="nm" id="phFrom">Messages<span>Safaricom</span></div>
              <svg class="inf" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
            </div>
            <div class="ph-m" id="msgs"><div class="ph-e">Press <b>Send reminders</b> to see the exact message your tenant gets.</div></div>
            <div class="ph-in">
              <span class="fake">Text message</span>
              <span class="snd"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20V5M5 12l7-7 7 7"/></svg></span>
            </div>
            <div class="ph-home"></div>
          </div>
        </div>
      </div>
      <p class="tiny" style="margin-top:14px;max-width:340px">Every message carries the unit number and the paybill. Works on any phone. No app and no data bundle.</p>
    </div>
  </div>
</section>
<!-- LAW 15 · The frame. One number, four things to stand it next to. -->
<section class="sec on-dark" id="cost">
  <div class="band-img" style="background-image:url('{{ $img['cost'] }}');opacity:.16;z-index:0"></div>
  <div class="head" style="position:relative;z-index:2">
    <h2 class="lg">Is KES 3,750<br>a month worth it?</h2>
    <p class="sm">That is the flat price for up to 75 units. Here is how it compares to things you are already paying for.</p>
  </div>
  <div class="frame" style="position:relative;z-index:2">
    <div class="price">
      <div class="n">3,750</div>
      <p class="tiny" style="margin-top:12px">Kenya shillings a month, up to 75 units</p>
    </div>
    <div class="right">
      <div class="tabs" role="tablist">
        <button class="tab" role="tab" aria-selected="true"  data-f="0">One late tenant</button>
        <button class="tab" role="tab" aria-selected="false" data-f="1">One empty unit</button>
        <button class="tab" role="tab" aria-selected="false" data-f="2">Hiring someone</button>
        <button class="tab" role="tab" aria-selected="false" data-f="3">Per day</button>
      </div>
      <div class="frame-out" id="frameOut" aria-live="polite"></div>
    </div>
  </div>
</section>
<!-- LAW 14 · Friction. They build the number, so the number is theirs. -->
<section class="sec on-green">
  <div class="head">
    <h2 class="lg">What is late rent<br>costing you?</h2>
    <p class="sm">Answer three quick questions and we will work it out with your own numbers. Takes under a minute. No email and no sign up.</p>
  </div>
  <div class="aud">
    <div class="aud-s">
      <div class="on" id="s1">1 <span class="lbl">Units</span></div>
      <div id="s2">2 <span class="lbl">Rent</span></div>
      <div id="s3">3 <span class="lbl">Late</span></div>
      <div id="s4">4 <span class="lbl">Result</span></div>
    </div>
    <div class="aud-p" id="p1">
      <div class="aud-q">How many units do you collect rent from?</div>
      <div class="aud-in"><input type="number" id="qUnits" min="1" max="5000" value="24" inputmode="numeric" aria-label="Units"><span class="u">units</span></div>
      <div class="chips">
        <button class="chip" data-set="qUnits" data-val="8">8</button>
        <button class="chip" data-set="qUnits" data-val="24">24</button>
        <button class="chip" data-set="qUnits" data-val="60">60</button>
        <button class="chip" data-set="qUnits" data-val="180">180</button>
      </div>
      <div class="aud-n"><button class="btn inv s" data-go="2">Next</button></div>
    </div>
    <div class="aud-p" id="p2" hidden>
      <div class="aud-q">What does one unit pay you a month?</div>
      <div class="aud-in"><span class="u">KES</span><input type="number" id="qRent" min="500" max="2000000" step="500" value="12000" inputmode="numeric" aria-label="Average rent"></div>
      <div class="chips">
        <button class="chip" data-set="qRent" data-val="6500">Bedsitter</button>
        <button class="chip" data-set="qRent" data-val="12000">One bed</button>
        <button class="chip" data-set="qRent" data-val="25000">Two bed</button>
        <button class="chip" data-set="qRent" data-val="45000">Commercial</button>
      </div>
      <div class="aud-n"><button class="btn inv s" data-go="3">Next</button><button class="back" data-go="1">Back</button></div>
    </div>
    <div class="aud-p" id="p3" hidden>
      <div class="aud-q">In a normal month, how many pay late?</div>
      <div class="aud-in"><input type="number" id="qLate" min="0" max="100" value="30" inputmode="numeric" aria-label="Percent late"><span class="u">out of every 100</span></div>
      <div class="chips">
        <button class="chip" data-set="qLate" data-val="10">10</button>
        <button class="chip" data-set="qLate" data-val="30">30</button>
        <button class="chip" data-set="qLate" data-val="50">50</button>
        <button class="chip" data-set="qLate" data-val="70">70</button>
      </div>
      <div class="aud-n"><button class="btn inv s" data-go="4">Show me</button><button class="back" data-go="2">Back</button></div>
    </div>
    <div class="aud-p" id="p4" hidden>
      <p class="tiny" id="rTop" style="margin-bottom:14px"></p>
      <div class="aud-big" id="rBig">KES 0</div>
      <p class="sm" id="rSub" style="margin-top:14px;color:rgba(255,255,255,.7)"></p>
      <div class="aud-lines" id="rLines"></div>
      <p class="aud-v" id="rSay"></p>
      <div class="aud-n"><a href="{{ route('register.step1') }}" class="btn inv s">Start free trial</a><button class="back" data-go="1">Change answers</button></div>
    </div>
  </div>
</section>
<!-- LAW 13 · The small things. One line each. -->
<section class="sec rule">
  <div class="head">
    <h2 class="lg">The details that<br>get you paid.</h2>
    <p class="sm">These are the small things your tenants actually notice. They are also the reason rent turns up on the 3rd instead of the 13th.</p>
  </div>
  <div class="split">
    <div class="frm" style="aspect-ratio:3/4">
      <img src="{{ $img['detail'] }}" alt="A rental apartment block in Nairobi, Kenya" loading="lazy">
      <span class="cap">Affordable & Reliable</span>
    </div>
    <div class="list">
      @foreach([
        ['Every message says which unit','Your tenants stop replying to ask which house they are paying for.'],
        ['Payments land on the right unit','NyumbaPc matches on the account number, so Otieno&rsquo;s rent never clears Wanjiru&rsquo;s balance.'],
        ['Your caretaker sees only his property','He can record what he collects without seeing what you earn.'],
        ['Tenants send their own proof','A bank payment arrives attached to their account instead of lost in your WhatsApp.'],
        ['Works on any phone','Reminders go by SMS. Your tenant needs no app, no data bundle and no password.'],
        ['Part payments stay open','If someone pays half the rent, the balance keeps showing what is still owed.'],
        ['Statements you can send','Download a PDF for each owner instead of building it again in Excel.'],
        ['Cash is still counted','Record it against the unit and the balance updates the same way.'],
      ] as [$t,$s])
        <div class="li"><h4>{{ $t }}</h4><p>{!! $s !!}</p></div>
      @endforeach
    </div>
  </div>
</section>
<section class="band tall">
  <div class="band-img" style="background-image:url('{{ $img['band2'] }}')"></div>
  <div class="band-veil"></div>
  <div class="band-in"><h2 class="md">One flat price while you are small. Per unit once you grow.</h2></div>
</section>
<!-- Pricing -->
<section class="sec" id="pricing">
  <div class="head">
    <h2 class="lg">Simple pricing.</h2>
    <p class="sm">One flat price while you are small. Per unit once you grow. No setup fee, and you can leave whenever you want.</p>
  </div>
  <div class="plans">
    @php
      $plans = [
        ['Starter','KES 3,750','flat, per month','KES 125 a day','For up to 75 units.',
          ['Up to 75 units','300 SMS credits','PDF invoices and receipts','Utility and meter billing','Tenant portal'],false],
        ['Growth','KES 50','per unit, per month','KES 1.60 per unit a day','76 to 150 units.',
          ['76 to 150 units','4 SMS credits per unit','Bulk invoicing','Advanced reports','Caretaker logins'],true],
        ['Enterprise','KES 40','per unit, per month','Less than a receipt book','151 units and above.',
          ['151 units and above','4 SMS credits per unit','Per-owner statements','Dedicated support','API access'],false],
      ];
    @endphp
    @foreach($plans as [$name,$amt,$per,$cmp,$desc,$feats,$pick])
      <div class="plan {{ $pick ? 'pick' : '' }}">
        <div class="pn">{{ $name }} @if($pick)<span class="pk">Most chosen</span>@endif</div>
        <div class="amt">{{ $amt }}</div>
        <div class="per">{{ $per }} &middot; {{ $desc }}</div>
        <div class="cmp">{{ $cmp }}</div>
        <ul>@foreach($feats as $f)<li>{{ $f }}</li>@endforeach</ul>
        <div class="go"><a href="{{ $name === 'Enterprise' ? 'mailto:info@NyumbaPc.co.ke' : route('register.step1') }}">{{ $name === 'Enterprise' ? 'Talk to us' : 'Start free trial' }}</a></div>
      </div>
    @endforeach
  </div>
  <div class="calc">
    <label for="cUnits">Your units</label>
    <input type="number" id="cUnits" min="1" max="10000" value="25" inputmode="numeric">
    <div class="out"><b id="cPrice">KES 3,750</b><span id="cMeta">Starter &middot; 300 SMS credits</span></div>
  </div>
  <p class="tiny" style="margin-top:18px">Pay 6 months, get 1 free. Pay 12, get 2 free. Extra SMS at KES 1.</p>
</section>
<!-- FAQ -->
<section class="sec rule">
  <div class="head"><h2 class="lg">Questions.</h2></div>
  <div style="max-width:820px">
    @foreach([
      ['Does NyumbaPc work with M-Pesa?','Yes. Tenants pay to your paybill using their unit number as the account number. NyumbaPc reads that account number and clears the right invoice on its own, so you are not scrolling through payment messages trying to work out who sent what.'],
      ['Do my tenants need a smartphone?','No. Every reminder and receipt goes out as a normal SMS, so any handset works. There is a tenant portal for those who want it, but nothing depends on them using it.'],
      ['What if a tenant pays in cash?','You or your caretaker record it against the unit, and the balance updates exactly as it would for an M-Pesa payment. NyumbaPc is not trying to push everyone onto one payment method.'],
      ['What if they only pay part of the rent?','It is recorded as a part payment and the balance stays open for the rest. Reminders keep going out for what is still owed, not the full amount again.'],
      ['Can I manage properties for other landlords?','Yes. Each owner gets their own books, their own statements and their own login, and you run them all from one account. You can send each owner a PDF statement instead of rebuilding it in Excel every month.'],
      ['How long does setup take?','Send us your unit and tenant list as a spreadsheet, or even a photo of your book, and we import it for you. We then go through your first invoice run together before a single tenant is messaged.'],
      ['What happens to my data if I stop paying?','It stays yours and you can export it. Nothing is deleted out from under you. The trial is 7 days with every feature and no card, so you can find out whether it fits before any money changes hands.'],
    ] as $i => [$qq,$aa])
      <details class="q" @if($i === 0) open @endif><summary>{{ $qq }}</summary><div class="a">{{ $aa }}</div></details>
    @endforeach
  </div>
</section>
<!-- CTA -->
<section class="band tall" style="align-items:center">
  <div class="band-img" style="background-image:url('{{ $img['cta'] }}')"></div>
  <div class="band-veil" style="background:rgba(20,17,15,.76)"></div>
  <div class="band-in" style="padding-top:clamp(64px,10vw,144px);padding-bottom:clamp(64px,10vw,144px)">
    <h2 class="lg" style="max-width:12ch">Try it free<br>for 7 days.</h2>
    <div class="hero-row">
      <a href="{{ route('register.step1') }}" class="btn gold">Start free trial</a>
      <a href="https://wa.me/254705056343" class="btn inv-alt">WhatsApp us</a>
    </div>
    <p class="tiny" style="margin-top:24px;color:var(--mute-d)">Every feature, no card needed. We import your units for you and check your first invoice run with you.</p>
  </div>
</section>
<footer class="foot on-dark">
  <div class="foot-g">
    <div>
      <img src="{{ $img['logo'] }}" alt="NyumbaPc">
      <p class="tiny" style="max-width:26ch;color:var(--mute-d)">Rent collection software for landlords, property managers and agents in Kenya.</p>
    </div>
    <div>
      <div class="fh">Product</div>
      <a class="fa" href="#book">How it works</a>
      <a class="fa" href="#try">Try it</a>
      <a class="fa" href="#pricing">Pricing</a>
      <a class="fa" href="{{ route('register.step1') }}">Start free trial</a>
    </div>
    <div>
      <div class="fh">Access</div>
      <a class="fa" href="{{ route('login') }}">Sign in</a>
      <a class="fa" href="{{ route('portal.login') }}">Tenant login</a>
      <a class="fa" href="{{ route('privacy') }}">Privacy</a>
      <a class="fa" href="{{ route('terms') }}">Terms</a>
      <a class="fa" href="{{ route('cookies') }}">Cookie Policy</a>
      <a class="fa" href="#" onclick="nyumbaCookieSettings();return false;">Cookie settings</a>
    </div>
    <div>
      <div class="fh">Talk to us</div>
      <a class="fa" href="https://wa.me/254705056343">WhatsApp 0705 056 343</a>
      <a class="fa" href="mailto:info@NyumbaPc.co.ke">info@NyumbaPc.co.ke</a>
      <span class="fa">Nairobi, Kenya</span>
    </div>
  </div>
  <div class="fb"><span>&copy; {{ date('Y') }} NyumbaPc</span><span>A product of Edroniq Technologies</span></div>
</footer>
@verbatim
<script>
(function () {
  'use strict';
  var slow = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var kes = function (n) { return 'KES ' + Math.round(n).toLocaleString('en-KE'); };
  var $ = function (id) { return document.getElementById(id); };

  /* LAW 15. The price never moves, only what it stands beside. */
  var frames = [
    'One tenant owing you KES 12,000 for a month costs more than three months of NyumbaPc.',
    'A KES 25,000 unit sitting empty for one month costs more than six months of NyumbaPc.',
    'Paying someone KES 25,000 a month to chase rent costs almost seven times more.',
    'It works out to KES 125 a day. About the same as fare into town.'
  ];
  var out = $('frameOut'), tabs = [].slice.call(document.querySelectorAll('.tab'));
  function frame(i) {
    out.innerHTML = '<p class="q">' + frames[i] + '</p>';
    tabs.forEach(function (t, j) { t.setAttribute('aria-selected', j === i ? 'true' : 'false'); });
  }
  tabs.forEach(function (t) { t.addEventListener('click', function () { frame(+t.dataset.f); }); });
  if (out) frame(0);

  /* Shared pricing logic, used by the audit and the calculator. */
  function planFor(u) {
    if (u <= 75)  return { n:'Starter',    c:3750,     s:300 };
    if (u <= 150) return { n:'Growth',     c:u * 50,   s:u * 4 };
    return              { n:'Enterprise', c:u * 40,   s:u * 4 };
  }

  /* LAW 14. Three questions before an answer. The friction is the point. */
  var MINS = 15;
  function go(n) {
    [1,2,3,4].forEach(function (i) {
      var p = $('p' + i), s = $('s' + i);
      if (p) p.hidden = (i !== n);
      if (s) { s.classList.toggle('on', i === n); s.classList.toggle('done', i < n); }
    });
    if (n === 4) result();
  }
  function line(l, v) { return '<div class="aud-line"><span>' + l + '</span><b>' + v + '</b></div>'; }
  function result() {
    var u = Math.max(1, parseInt($('qUnits').value, 10) || 1);
    var r = Math.max(1, parseInt($('qRent').value, 10) || 1);
    var pc = Math.min(100, Math.max(0, parseInt($('qLate').value, 10) || 0));
    var late = pc > 0 ? Math.max(1, Math.round(u * pc / 100)) : 0;
    var m = late * r, plan = planFor(u), need = Math.max(1, Math.ceil(plan.c / r));
    var hrs = Math.round(late * MINS * 12 / 60);
    $('rTop').textContent = 'Based on ' + u + ' units at about ' + kes(r) + ' each, with ' + pc + ' in every 100 paying late';
    $('rBig').textContent = kes(m);
    $('rSub').textContent = late === 0
      ? 'Nothing late at all. Either your tenants are unusual, or you spend your whole month making sure of it.'
      : 'That is what is still outside your account on the 6th of a normal month, spread across ' + late + ' ' + (late === 1 ? 'tenant' : 'tenants') + '.';
    $('rLines').innerHTML =
      line('Tenants paying late each month', late + ' of ' + u) +
      line('Rent that comes in late each year', kes(m * 12)) +
      line('Hours a year spent following up, at ' + MINS + ' minutes each', hrs + ' hrs') +
      line('What NyumbaPc costs you, ' + plan.n + ' plan', kes(plan.c) + ' a month');
    $('rSay').innerHTML = late === 0
      ? 'Then it is your time, not your money. You get back <b>' + hrs + ' hours a year</b> that currently go into following people up.'
      : 'Get just <b>' + need + ' of those ' + late + '</b> paying on time and NyumbaPc has already paid for itself.';
  }
  document.querySelectorAll('[data-go]').forEach(function (b) {
    b.addEventListener('click', function () { go(+b.dataset.go); });
  });
  document.querySelectorAll('.chip').forEach(function (c) {
    c.addEventListener('click', function () { var i = $(c.dataset.set); if (i) { i.value = c.dataset.val; i.focus(); } });
  });

  /* LAW 17. A real rent cycle, run by them, nothing asked in return. */
  var BASE = [
    { n:'Wanjiru Kamau',  u:'A1', d:12000, s:'Due 5 Sep',   c:'TI4KM2QX8Z', k:'8Q2M' },
    { n:'Otieno Ochieng', u:'A2', d:12000, s:'4 days late', c:'TI4LN7RB1C', k:'5V7K' },
    { n:'Mueni Musyoka',  u:'A3', d:9000,  s:'Part paid',   c:'TI4MP3VD9E', k:'3H9P' }
  ];
  var T, elT = $('tenants'), elSum = $('sum'), elLog = $('log'), elMsg = $('msgs'),
      elFrom = $('phFrom'), elAv = $('phAv'), elHint = $('hint'),
      bR = $('remind'), bP = $('pay'), bX = $('reset');

  /* The handset clock runs on Nairobi time, like the tenant's own phone. */
  function nairobiTime() {
    try {
      return new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Africa/Nairobi'
      }).format(new Date());
    } catch (e) {
      var d = new Date();
      return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }
  }
  var phClock = $('phClock');
  if (phClock) {
    phClock.textContent = nairobiTime();
    setInterval(function () { phClock.textContent = nairobiTime(); }, 20000);
  }

  /* Whose inbox we are looking at. */
  function setContact(name, sub) {
    if (elFrom) elFrom.innerHTML = name + '<span>' + sub + '</span>';
    if (elAv) elAv.textContent = name === 'Messages' ? '?' : name.charAt(0).toUpperCase();
  }

  function dayDivider() {
    if (!elMsg || elMsg.querySelector('.ph-day')) return;
    var d = document.createElement('div');
    d.className = 'ph-day';
    d.textContent = 'Today ' + nairobiTime();
    elMsg.appendChild(d);
  }
  function showTyping() {
    if (!elMsg || elMsg.querySelector('.typing')) return;
    var t = document.createElement('div');
    t.className = 'typing';
    t.innerHTML = '<i></i><i></i><i></i>';
    elMsg.appendChild(t);
    elMsg.scrollTop = elMsg.scrollHeight;
  }
  function hideTyping() {
    var t = elMsg && elMsg.querySelector('.typing');
    if (t) t.remove();
  }
  function draw(hit) {
    elT.innerHTML = T.map(function (t) {
      var paid = t.d === 0;
      return '<div class="sb-t' + (t.u === hit ? ' hit' : '') + '">' +
        '<div><div class="nm">' + t.n + '</div><div class="mt">Unit ' + t.u + ' &middot; ' +
        (t.r ? 'reminded' : 'not reminded') + '</div></div>' +
        '<div class="bal' + (paid ? ' zero' : '') + '">' + kes(t.d) + '<span class="st">' + t.s + '</span></div></div>';
    }).join('');
    var o = T.reduce(function (s, t) { return s + t.d; }, 0);
    var done = T.filter(function (t) { return t.d === 0; }).length;
    elSum.textContent = o === 0 ? 'Every unit settled' : T.length + ' tenants · ' + done + ' settled · ' + kes(o) + ' outstanding';
    bP.disabled = (o === 0);
    elHint.textContent = o === 0 ? 'That is the whole month done.' : (T[0].r ? 'Now record a payment coming in' : 'Start with the reminders');
  }
  function log(h) {
    elLog.classList.add('on');
    var d = document.createElement('div'); d.className = 'e'; d.innerHTML = h;
    elLog.insertBefore(d, elLog.firstChild);
  }
  function bub(html, me, wait) {
    function place() {
      var e = elMsg.querySelector('.ph-e'); if (e) e.remove();
      dayDivider();
      hideTyping();
      var b = document.createElement('div');
      b.className = 'bub' + (me ? ' me' : '');
      b.innerHTML = html;
      elMsg.appendChild(b);
      elMsg.scrollTop = elMsg.scrollHeight;
    }
    if (slow) { place(); return; }
    // Incoming messages get typing dots first, the way a real thread behaves.
    if (!me) {
      setTimeout(function () {
        var e = elMsg.querySelector('.ph-e'); if (e) e.remove();
        dayDivider();
        showTyping();
      }, Math.max(0, wait - 650));
    }
    setTimeout(place, wait);
  }
  function reset() {
    T = BASE.map(function (t) { return Object.assign({}, t, { r:false }); });
    elMsg.innerHTML = '<div class="ph-e">Press <b>Send reminders</b> to see what your tenant actually receives.</div>';
    setContact('Messages', 'Safaricom');
    elLog.innerHTML = ''; elLog.classList.remove('on');
    bR.disabled = false;
    draw();
  }
  if (bR) bR.addEventListener('click', function () {
    bR.disabled = true;
    T.forEach(function (t, i) {
      if (t.d === 0) return;
      t.r = true;
      setTimeout(function () { log('SMS sent to ' + t.n + ', Unit ' + t.u + ' &middot; ' + kes(t.d) + ' due'); draw(); }, slow ? 0 : i * 340);
    });
    var w = T[0];
    setContact(w.n.split(' ')[0], 'Unit ' + w.u + ' &middot; Riverside Court');
    bub('<span class="who">NyumbaPc</span>Hi ' + w.n.split(' ')[0] + ', your rent for Unit ' + w.u +
        ' is ' + kes(w.d) + ', due 5 Sep. Pay to Paybill 4071234, account <b>' + w.u +
        '</b>. Asante.<span class="tm">' + nairobiTime() + '</span>', false, 520);
  });
  if (bP) bP.addEventListener('click', function () {
    var t = T.filter(function (x) { return x.d > 0; })[0];
    if (!t) return;
    var amt = t.d, first = t.n.split(' ')[0];
    setContact(first, 'Unit ' + t.u + ' &middot; Riverside Court');
    bub('Nimetuma. ' + t.u + '.<span class="tm">' + nairobiTime() + ' &middot; Delivered</span>', true, 150);
    bub('<span class="code mono">' + t.c + '</span> Confirmed. Ksh' + amt.toLocaleString('en-KE') +
        '.00 sent to NyumbaPc PROPERTY 4071234 for account ' + t.u + '.<span class="tm">M-PESA &middot; 09:14</span>', false, slow ? 0 : 800);
    bub('<span class="who">NyumbaPc</span>Received, ' + first + '. ' + kes(amt) + ' for Unit ' + t.u +
        '. Your balance is now KES 0. Receipt: nyumbapc.co.ke/r/' + t.k + '<span class="tm">09:14</span>', false, slow ? 0 : 1600);
    setTimeout(function () {
      t.d = 0; t.s = 'Cleared';
      draw(t.u);
      log('<b>' + kes(amt) + '</b> matched to Unit ' + t.u + ' using the account number.');
      setTimeout(function () { draw(); }, 1300);
    }, slow ? 0 : 1000);
  });
  if (bX) bX.addEventListener('click', reset);
  if (elT) reset();

  /* Pricing calculator */
  var cu = $('cUnits');
  function calc() {
    var u = Math.max(1, parseInt(cu.value, 10) || 1), p = planFor(u);
    $('cPrice').textContent = kes(p.c);
    $('cMeta').textContent = p.n + ' · ' + p.s.toLocaleString('en-KE') + ' SMS credits';
  }
  if (cu) { cu.addEventListener('input', calc); calc(); }

  /* Nav */
  var b = $('burger'), l = $('navL');
  if (b && l) {
    b.addEventListener('click', function () {
      var o = l.classList.toggle('open');
      b.classList.toggle('open', o);
      b.setAttribute('aria-expanded', o ? 'true' : 'false');
    });
    l.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { l.classList.remove('open'); b.classList.remove('open'); });
    });
  }
})();
</script>
@endverbatim
@include('partials.cookie-consent')
</body>
</html>