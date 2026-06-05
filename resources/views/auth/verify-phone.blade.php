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
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:460px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        h1{font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:8px}
        .email-badge{display:inline-block;background:#e6f2ed;color:#1a6b52;font-size:13px;font-weight:500;padding:7px 14px;border-radius:8px;margin:10px 0 18px}
        .desc{font-size:13px;color:#8a8880;line-height:1.65;margin-bottom:22px}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px}
        input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#1a6b52}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:14px;transition:background .2s}
        .btn:hover{background:#155c45}
        .btn:disabled{background:#a0c4b8;cursor:not-allowed}
        .btn-outline{width:100%;height:42px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,.12);border-radius:8px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:10px;transition:all .2s}
        .btn-outline:hover{border-color:#1a6b52;color:#1a6b52}
        #error-box{display:none;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#991b1b}
        #success-box{display:none;background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#166534}
        .divider{height:1px;background:rgba(0,0,0,.07);margin:22px 0}
        .sign-out{display:block;text-align:center;font-size:13px;color:#8a8880;text-decoration:none;margin-top:16px}
        .sign-out:hover{color:#b91c1c}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" style="height:44px;object-fit:contain;display:block;margin-bottom:22px">

    <div style="font-size:40px;margin-bottom:14px">📬</div>
    <h1>Verify your email</h1>
    <div class="email-badge">{{ auth()->user()->email }}</div>

    <p class="desc">
        Your email address needs to be verified before you can access the dashboard.
        Enter your password below so we can send you a fresh verification link.
    </p>

    <div id="error-box"></div>
    <div id="success-box"></div>

    <div id="auth-section">
        <div style="margin-bottom:14px">
            <label>Your password</label>
            <input type="password" id="re-password" placeholder="Enter your password"
                   onkeydown="if(event.key==='Enter') checkStatus()">
        </div>
        <button class="btn" id="check-btn" onclick="checkStatus()">
            Check verification status
        </button>
        <button class="btn-outline" id="resend-btn" onclick="resendAndCheck()" disabled>
            Send verification email
        </button>
        <div id="resend-countdown" style="text-align:center;font-size:12px;color:#8a8880;margin-top:8px"></div>
    </div>

    <div class="divider"></div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sign-out">Sign out and use a different account</button>
    </form>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, signInWithEmailAndPassword, sendEmailVerification } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const auth = getAuth(initializeApp({
    apiKey: "AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U",
    authDomain: "nyumba-d932c.firebaseapp.com",
    projectId: "nyumba-d932c",
    storageBucket: "nyumba-d932c.firebasestorage.app",
    messagingSenderId: "268571108072",
    appId: "1:268571108072:web:23489e5de76de12e579f5d"
}));

const userEmail = '{{ auth()->user()->email }}';
let firebaseUser = null;

function showError(msg) {
    document.getElementById('error-box').textContent = msg;
    document.getElementById('error-box').style.display = 'block';
    document.getElementById('success-box').style.display = 'none';
}
function showSuccess(msg) {
    document.getElementById('success-box').textContent = msg;
    document.getElementById('success-box').style.display = 'block';
    document.getElementById('error-box').style.display = 'none';
}
function csrfToken() { return document.querySelector('meta[name="csrf-token"]').content; }

window.checkStatus = async function() {
    const password = document.getElementById('re-password').value;
    const checkBtn = document.getElementById('check-btn');
    const resendBtn = document.getElementById('resend-btn');

    if (!password) { showError('Please enter your password.'); return; }

    checkBtn.disabled = true; checkBtn.textContent = 'Checking...';

    try {
        const cred = await signInWithEmailAndPassword(auth, userEmail, password);
        firebaseUser = cred.user;
        await firebaseUser.reload();

        resendBtn.disabled = false;

        if (firebaseUser.emailVerified) {
            const token = await firebaseUser.getIdToken(true);
            const res   = await fetch('/auth/mark-verified', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ id_token: token }),
            });
            const data = await res.json();
            if (data.redirect) { window.location.href = data.redirect; return; }
            showError(data.error || 'Something went wrong. Please try again.');
        } else {
            showError('Email not verified yet. Click "Send verification email" below, then check your inbox and click the link.');
        }
    } catch(e) {
        const map = {
            'auth/wrong-password': 'Incorrect password. Please try again.',
            'auth/invalid-credential': 'Incorrect password. Please try again.',
            'auth/too-many-requests': 'Too many attempts. Please try again later.',
        };
        showError(map[e.code] || 'Something went wrong. Please try again.');
    }

    checkBtn.disabled = false; checkBtn.textContent = 'Check verification status';
};

window.resendAndCheck = async function() {
    if (!firebaseUser) { showError('Please enter your password first and click Check verification status.'); return; }
    const btn = document.getElementById('resend-btn');
    btn.disabled = true;
    try {
        await sendEmailVerification(firebaseUser);
        showSuccess('Verification email sent. Click the link in your inbox then come back and click Check verification status.');
        startCountdown();
    } catch(e) {
        showError('Failed to send verification email. Please try again.');
        btn.disabled = false;
    }
};

function startCountdown() {
    let s = 60;
    const counter = document.getElementById('resend-countdown');
    const btn     = document.getElementById('resend-btn');
    btn.disabled  = true;
    counter.textContent = `Resend available in ${s}s`;
    const t = setInterval(() => {
        s--;
        counter.textContent = `Resend available in ${s}s`;
        if (s <= 0) { clearInterval(t); counter.textContent = ''; btn.disabled = false; }
    }, 1000);
}
</script>
</body>
</html>