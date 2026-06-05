<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify your email — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06)}
        .logo{display:block;height:40px;object-fit:contain;margin:0 auto 32px}
        h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:8px}
        .badge{display:inline-block;background:#e6f2ed;color:#1a6b52;font-size:13px;font-weight:500;padding:6px 14px;border-radius:7px;margin:8px 0 16px}
        .desc{font-size:13px;color:#8a8880;line-height:1.6;margin-bottom:22px}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s}
        .btn:hover{background:#155c45}
        .btn:disabled{background:#a0c4b8;cursor:not-allowed}
        .btn-outline{width:100%;height:42px;background:transparent;color:#1a6b52;border:1.5px solid #1a6b52;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:10px;transition:all .2s}
        .btn-outline:hover{background:#f5fbf9}
        .btn-outline:disabled{border-color:#e5e3de;color:#c4c2be;cursor:not-allowed}
        .err{display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 13px;font-size:13px;color:#dc2626;margin-bottom:14px}
        .ok{display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:9px 13px;font-size:13px;color:#16a34a;margin-bottom:14px}
        .checking{text-align:center;font-size:13px;color:#8a8880;margin-bottom:16px}
        .spinner{display:inline-block;width:14px;height:14px;border:2px solid #e5e3de;border-top-color:#1a6b52;border-radius:50%;animation:spin .7s linear infinite;margin-right:6px;vertical-align:middle}
        @keyframes spin{to{transform:rotate(360deg)}}
        .divider{height:1px;background:rgba(0,0,0,.07);margin:22px 0}
        .sign-out{width:100%;background:none;border:none;font-size:13px;color:#8a8880;cursor:pointer;font-family:'DM Sans',sans-serif;padding:0}
        .sign-out:hover{color:#b91c1c}
        .countdown{font-size:12px;color:#c4c2be;text-align:center;margin-top:6px}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <h1>Verify your email</h1>
    <div class="badge">{{ auth()->user()->email }}</div>
    <p class="desc">Your email address needs to be verified before you can access the dashboard.</p>

    <div id="checking-state" class="checking">
        <span class="spinner"></span> Checking verification status...
    </div>

    <div id="err-box" class="err"></div>
    <div id="ok-box" class="ok"></div>

    <div id="action-section" style="display:none">
        <button class="btn" id="btn-send" onclick="sendVerification()">Send verification email</button>
        <button class="btn-outline" id="btn-check" onclick="checkVerification()" disabled>I have verified my email</button>
        <div class="countdown" id="cd"></div>
    </div>

    <div class="divider"></div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sign-out">Sign out</button>
    </form>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, signInWithEmailAndPassword, sendEmailVerification, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const auth = getAuth(initializeApp({
    apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U",
    authDomain:"nyumba-d932c.firebaseapp.com",
    projectId:"nyumba-d932c",
    storageBucket:"nyumba-d932c.firebasestorage.app",
    messagingSenderId:"268571108072",
    appId:"1:268571108072:web:23489e5de76de12e579f5d"
}));

const CONTINUE_URL = window.location.origin + '/auth/verified-callback';
const userEmail    = '{{ auth()->user()->email }}';
let fbUser = null;

function csrf()    { return document.querySelector('meta[name="csrf-token"]').content; }
function showErr(m){ const e=document.getElementById('err-box'); e.textContent=m; e.style.display='block'; document.getElementById('ok-box').style.display='none'; }
function showOk(m) { const e=document.getElementById('ok-box');  e.textContent=m; e.style.display='block'; document.getElementById('err-box').style.display='none'; }
function showActions(){ document.getElementById('checking-state').style.display='none'; document.getElementById('action-section').style.display='block'; }

async function markVerified(user) {
    try {
        const token = await user.getIdToken(true);
        const res   = await fetch('/auth/mark-verified', {
            method:  'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf() },
            body:    JSON.stringify({ id_token: token }),
        });
        if (!res.ok) {
            showErr('Server error (' + res.status + '). Please try again.');
            return false;
        }
        const data = await res.json();
        if (data.redirect) { window.location.href = data.redirect; return true; }
        showErr(data.error || 'Something went wrong. Please try again.');
        return false;
    } catch(e) {
        showErr('Network error. Please check your connection and try again.');
        return false;
    }
}

// On load — auto-detect if already verified
onAuthStateChanged(auth, async user => {
    if (user && user.email === userEmail) {
        fbUser = user;
        try {
            await user.reload();
            if (user.emailVerified) {
                document.getElementById('checking-state').innerHTML =
                    '<span class="spinner"></span> Email verified. Redirecting...';
                const ok = await markVerified(user);
                if (!ok) showActions();
                return;
            }
        } catch(e) {
            // Could not reload, fall through to show actions
        }
    }
    showActions();
});

window.sendVerification = async function() {
    const btn = document.getElementById('btn-send');
    btn.disabled = true; btn.textContent = 'Sending...';

    if (!fbUser) {
        const pass = window.prompt('Enter your password to send the verification email:');
        if (!pass) { btn.disabled=false; btn.textContent='Send verification email'; return; }
        try {
            const c = await signInWithEmailAndPassword(auth, userEmail, pass);
            fbUser = c.user;
        } catch(e) {
            const map = {
                'auth/wrong-password':     'Incorrect password.',
                'auth/invalid-credential': 'Incorrect password.',
                'auth/too-many-requests':  'Too many attempts. Try again later.',
            };
            showErr(map[e.code] || 'Authentication failed. Please try again.');
            btn.disabled=false; btn.textContent='Send verification email';
            return;
        }
    }

    try {
        await sendEmailVerification(fbUser, { url: CONTINUE_URL });
        showOk('Verification email sent. Check your inbox and click the link. This page will redirect automatically once verified.');
        document.getElementById('btn-check').disabled = false;
        countdown();
    } catch(e) {
        const map = { 'auth/too-many-requests':'Please wait before requesting another email.' };
        showErr(map[e.code] || 'Failed to send. Please try again.');
        btn.disabled=false; btn.textContent='Send verification email';
    }
};

window.checkVerification = async function() {
    const btn = document.getElementById('btn-check');
    btn.disabled=true; btn.textContent='Checking...';

    if (!fbUser) {
        showErr('Please send the verification email first.');
        btn.disabled=false; btn.textContent='I have verified my email';
        return;
    }

    try {
        await fbUser.reload();
        if (!fbUser.emailVerified) {
            showErr('Not verified yet. Please click the link in your inbox first.');
            btn.disabled=false; btn.textContent='I have verified my email';
            return;
        }
        const ok = await markVerified(fbUser);
        if (!ok) { btn.disabled=false; btn.textContent='I have verified my email'; }
    } catch(e) {
        showErr('Something went wrong. Please try again.');
        btn.disabled=false; btn.textContent='I have verified my email';
    }
};

function countdown() {
    let s = 60;
    const c   = document.getElementById('cd');
    const btn = document.getElementById('btn-send');
    btn.disabled = true;
    c.textContent = `Resend in ${s}s`;
    const t = setInterval(() => {
        s--;
        c.textContent = `Resend in ${s}s`;
        if (s <= 0) {
            clearInterval(t);
            c.textContent = '';
            btn.disabled = false;
            btn.textContent = 'Resend verification email';
        }
    }, 1000);
}
</script>
</body>
</html>