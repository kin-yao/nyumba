{{-- Cookie consent banner. Include once, just before </body>.
     Pairs with partials/consent-mode, which must sit before the GTM snippet.
     Self-contained: its own styles are prefixed .ck- so it cannot collide
     with the page it sits on. --}}
<style>
.ck-wrap{position:fixed;left:0;right:0;bottom:0;z-index:9000;padding:clamp(12px,3vw,24px);
  display:flex;justify-content:flex-start;pointer-events:none}
.ck-wrap[hidden]{display:none}
.ck{pointer-events:auto;background:#fff;border:1px solid #14110f;max-width:620px;width:100%;
  font-family:'Satoshi',system-ui,sans-serif;color:#14110f;
  animation:ck-up .35s cubic-bezier(.2,.8,.3,1) both}
@keyframes ck-up{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.ck-main{display:grid;grid-template-columns:1fr auto;gap:22px;padding:22px 24px;align-items:start}
.ck-h{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
.ck-h h2{font-size:20px;font-weight:900;letter-spacing:-.03em;margin:0}
.ck-x{background:none;border:none;cursor:pointer;padding:4px;line-height:0;color:#6f6a62}
.ck-x:hover{color:#14110f}
.ck p{font-size:14.5px;line-height:1.55;color:#4a443c;margin:0}
.ck p a{color:#0e3f30;text-decoration:underline;text-underline-offset:2px}
.ck-art{width:104px;height:104px;flex-shrink:0}
.ck-btns{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.ck-btn{font-family:inherit;font-size:13.5px;font-weight:700;padding:12px 20px;cursor:pointer;
  border:1px solid #14110f;background:#fff;color:#14110f;transition:background .15s,color .15s}
.ck-btn:hover{background:#14110f;color:#fff}
.ck-btn.ck-primary{background:#14110f;color:#fff}
.ck-btn.ck-primary:hover{background:#fff;color:#14110f}
.ck-btn.ck-quiet{border-color:#e3e1da;color:#6f6a62}
.ck-btn.ck-quiet:hover{border-color:#14110f;background:#fff;color:#14110f}
/* Settings panel */
.ck-set{border-top:1px solid #e3e1da;padding:20px 24px 22px}
.ck-set[hidden]{display:none}
.ck-row{display:grid;grid-template-columns:1fr auto;gap:16px;align-items:start;
  padding:14px 0;border-bottom:1px solid #e3e1da}
.ck-row:last-of-type{border-bottom:none}
.ck-row h3{font-size:14.5px;font-weight:700;margin:0 0 4px}
.ck-row p{font-size:13px;line-height:1.5}
.ck-lock{font-size:12px;font-weight:700;color:#0e3f30;white-space:nowrap;padding-top:2px}
.ck-tog{position:relative;width:44px;height:24px;flex-shrink:0;border:1px solid #14110f;
  background:#fff;cursor:pointer;padding:0;transition:background .18s}
.ck-tog::after{content:"";position:absolute;top:2px;left:2px;width:18px;height:18px;
  background:#14110f;transition:transform .18s}
.ck-tog[aria-checked="true"]{background:#0e3f30;border-color:#0e3f30}
.ck-tog[aria-checked="true"]::after{transform:translateX(20px);background:#fff}
@media(max-width:640px){
  .ck-main{grid-template-columns:1fr;padding:20px}
  .ck-art{display:none}
  .ck-set{padding:18px 20px 20px}
  .ck-btn{flex:1 1 100%;text-align:center;justify-content:center}
}
@media (prefers-reduced-motion:reduce){.ck{animation:none}}
</style>

<div class="ck-wrap" id="ckWrap" hidden role="dialog" aria-modal="false" aria-labelledby="ckTitle">
  <div class="ck">
    <div class="ck-main">
      <div>
        <div class="ck-h">
          <h2 id="ckTitle">Cookies</h2>
          <button class="ck-x" id="ckClose" aria-label="Close and reject non-essential cookies">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <p>We use cookies to keep you signed in and to understand how the site is used. Only the ones needed to run Nyumba are on by default. You choose the rest. See our <a href="{{ route('cookies') }}">Cookie Policy</a>.</p>
        <div class="ck-btns">
          <button class="ck-btn ck-primary" id="ckAccept">Accept all</button>
          <button class="ck-btn" id="ckReject">Reject non-essential</button>
          <button class="ck-btn ck-quiet" id="ckSettings" aria-expanded="false" aria-controls="ckPanel">Settings</button>
        </div>
      </div>
      <svg class="ck-art" viewBox="0 0 100 100" aria-hidden="true">
        <circle cx="50" cy="50" r="46" fill="#c2924f"/>
        <g fill="#14110f">
          <circle cx="34" cy="30" r="5"/><circle cx="62" cy="26" r="4"/><circle cx="72" cy="48" r="5.5"/>
          <circle cx="46" cy="52" r="4.5"/><circle cx="28" cy="58" r="4"/><circle cx="56" cy="72" r="5"/>
          <circle cx="76" cy="70" r="3.5"/><circle cx="38" cy="76" r="3.5"/>
        </g>
      </svg>
    </div>

    <div class="ck-set" id="ckPanel" hidden>
      <div class="ck-row">
        <div>
          <h3>Strictly necessary</h3>
          <p>Keeps you signed in and protects forms. Nyumba cannot work without these.</p>
        </div>
        <span class="ck-lock">Always on</span>
      </div>
      <div class="ck-row">
        <div>
          <h3>Analytics</h3>
          <p>Helps us see which pages people find useful. Never used for advertising.</p>
        </div>
        <button class="ck-tog" id="ckAnalytics" role="switch" aria-checked="false" aria-label="Analytics cookies"></button>
      </div>
      <div class="ck-btns">
        <button class="ck-btn ck-primary" id="ckSave">Save choices</button>
      </div>
    </div>
  </div>
</div>

@verbatim
<script>
(function () {
  'use strict';
  var KEY = 'nyumba_cookie_consent';
  var wrap = document.getElementById('ckWrap');
  if (!wrap) return;

  var panel = document.getElementById('ckPanel');
  var toggle = document.getElementById('ckAnalytics');

  function gtag(){ (window.dataLayer = window.dataLayer || []).push(arguments); }

  function save(analytics) {
    try {
      localStorage.setItem(KEY, JSON.stringify({
        analytics: analytics,
        at: new Date().toISOString(),
        v: 1
      }));
    } catch (e) {}

    gtag('consent', 'update', {
      analytics_storage: analytics ? 'granted' : 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    });

    wrap.hidden = true;
  }

  // Only ask if they have not already chosen.
  var stored = null;
  try { stored = JSON.parse(localStorage.getItem(KEY)); } catch (e) {}
  if (stored && typeof stored.analytics === 'boolean') return;

  wrap.hidden = false;

  document.getElementById('ckAccept').addEventListener('click', function () { save(true); });
  document.getElementById('ckReject').addEventListener('click', function () { save(false); });
  document.getElementById('ckClose').addEventListener('click', function () { save(false); });
  document.getElementById('ckSave').addEventListener('click', function () {
    save(toggle.getAttribute('aria-checked') === 'true');
  });

  document.getElementById('ckSettings').addEventListener('click', function () {
    var open = panel.hidden;
    panel.hidden = !open;
    this.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  toggle.addEventListener('click', function () {
    this.setAttribute('aria-checked', this.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
  });

  // Lets you add a "Cookie settings" link anywhere: href="#" onclick="nyumbaCookieSettings()"
  window.nyumbaCookieSettings = function () {
    wrap.hidden = false;
    panel.hidden = false;
    if (stored) toggle.setAttribute('aria-checked', stored.analytics ? 'true' : 'false');
  };
})();
</script>
@endverbatim