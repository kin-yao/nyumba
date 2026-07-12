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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check your inbox — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06)}
        .logo{display:block;height:40px;object-fit:contain;margin:0 auto 28px}
        .steps{display:flex;gap:5px;margin-bottom:24px}
        .step{height:2px;border-radius:1px;flex:1}
        .step.done{background:#1a6b52;opacity:.35}
        .step.active{background:#1a6b52}
        .step.inactive{background:#e5e3de}
        h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:12px}
        .badge{display:inline-block;background:#e6f2ed;color:#1a6b52;font-size:13px;font-weight:500;padding:6px 14px;border-radius:7px;margin-bottom:16px}
        .desc{font-size:13px;color:#8a8880;line-height:1.65;margin-bottom:24px}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s}
        .btn:hover{background:#155c45}
        .btn:disabled{background:#a0c4b8;cursor:not-allowed}
        .btn-link{display:block;text-align:center;margin-top:14px;font-size:13px;color:#8a8880}
        .btn-link button{background:none;border:none;color:#1a6b52;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif}
        .btn-link button:disabled{color:#c4c2be;cursor:default}
        .err{display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 13px;font-size:13px;color:#dc2626;margin-bottom:14px}
        .ok{display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:9px 13px;font-size:13px;color:#16a34a;margin-bottom:14px}
        .start-over{display:block;text-align:center;margin-top:18px;font-size:12px;color:#c4c2be;text-decoration:none}
        .start-over:hover{color:#8a8880}
        .countdown{font-size:12px;color:#c4c2be;margin-left:4px}
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
            Loading...
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
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <div class="steps">
        <div class="step done"></div>
        <div class="step active"></div>
        <div class="step inactive"></div>
        <div class="step inactive"></div>
    </div>

    <h1>Check your inbox</h1>
    <div class="badge">{{ session('reg.email') ?? session('firebase.email') ?? '' }}</div>
    <p class="desc">We sent a verification link to your email address. Click the link and you will be redirected to the next step automatically.</p>

    <div id="err-box" class="err"></div>
    <div id="ok-box" class="ok"></div>

    <button class="btn" id="btn-resend" onclick="resend()">Resend verification email</button>

    <div class="btn-link">
        Clicked the link on another device?
        <button id="btn-check" onclick="checkManually()">Continue</button>
        <span class="countdown" id="cd"></span>
    </div>

    <a href="{{ route('register.step1') }}" class="start-over">Start over with a different email</a>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, onAuthStateChanged, sendEmailVerification } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const auth = getAuth(initializeApp({ apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U", authDomain:"nyumba-d932c.firebaseapp.com", projectId:"nyumba-d932c", storageBucket:"nyumba-d932c.firebasestorage.app", messagingSenderId:"268571108072", appId:"1:268571108072:web:23489e5de76de12e579f5d" }));
const CONTINUE_URL = window.location.origin + '/auth/verified-callback';
let fbUser = null;

function csrf(){return document.querySelector('meta[name="csrf-token"]').content;}
function showErr(m){document.getElementById('err-box').textContent=m;document.getElementById('err-box').style.display='block';document.getElementById('ok-box').style.display='none';}
function showOk(m){document.getElementById('ok-box').textContent=m;document.getElementById('ok-box').style.display='block';document.getElementById('err-box').style.display='none';}

onAuthStateChanged(auth, user => { fbUser = user || null; });

window.resend = async function() {
    const btn=document.getElementById('btn-resend');
    if(!fbUser){showErr('Session expired. Please go back and register again.');return;}
    btn.disabled=true; btn.textContent='Sending...';
    try {
        await sendEmailVerification(fbUser,{url:CONTINUE_URL});
        showOk('Verification email sent. Check your inbox.');
        countdown();
    } catch(e){
        const map={'auth/too-many-requests':'Please wait a moment before requesting another email.'};
        showErr(map[e.code]||'Failed to send. Please try again.');
        btn.disabled=false; btn.textContent='Resend verification email';
    }
};

window.checkManually = async function() {
    const btn=document.getElementById('btn-check');
    if(!fbUser){showErr('Session expired. Please go back and register again.');return;}
    btn.disabled=true; btn.textContent='Checking...';
    showLoader('Verifying your email...');
    try {
        await fbUser.reload();
        if(!fbUser.emailVerified){
            hideLoader();
            showErr('Email not verified yet. Please click the link in your inbox first.');
            btn.disabled=false; btn.textContent='Continue';
            return;
        }
        const token=await fbUser.getIdToken(true);
        const r=await fetch('/auth/mark-verified',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},body:JSON.stringify({id_token:token})});
        const d=await r.json();
        if(d.redirect){window.location.href=d.redirect;}
        else{
            hideLoader();
            showErr(d.error||'Something went wrong.');
            btn.disabled=false; btn.textContent='Continue';
        }
    } catch(e){
        hideLoader();
        showErr('Something went wrong. Please try again.');
        btn.disabled=false; btn.textContent='Continue';
    }
};

function countdown(){
    let s=60; const c=document.getElementById('cd'),btn=document.getElementById('btn-resend');
    btn.disabled=true; c.textContent=`(resend in ${s}s)`;
    const t=setInterval(()=>{s--;c.textContent=`(resend in ${s}s)`;if(s<=0){clearInterval(t);c.textContent='';btn.disabled=false;btn.textContent='Resend verification email';}},1000);
}
</script>
</body>
</html>