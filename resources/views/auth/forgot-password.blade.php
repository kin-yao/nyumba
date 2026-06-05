<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06)}
        .logo{display:block;height:40px;object-fit:contain;margin:0 auto 32px}
        h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:6px}
        .sub{font-size:13px;color:#8a8880;margin-bottom:22px;line-height:1.6}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}
        input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.11);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#1a6b52}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s;margin-top:14px}
        .btn:hover{background:#155c45}
        .btn:disabled{background:#a0c4b8;cursor:not-allowed}
        .err{display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 13px;font-size:13px;color:#dc2626;margin-bottom:14px}
        .ok{display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;font-size:13px;color:#16a34a}
        .back{display:block;text-align:center;margin-top:18px;font-size:13px;color:#1a6b52;text-decoration:none;font-weight:500}
        .back:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <h1>Reset your password</h1>
    <p class="sub">Enter the email on your account and we will send you a reset link.</p>

    <div id="err-box" class="err"></div>
    <div id="ok-box" class="ok"></div>

    <div id="form-section">
        <div>
            <label>Email address</label>
            <input type="email" id="r-email" placeholder="you@example.com" autofocus
                   onkeydown="if(event.key==='Enter')sendReset()">
        </div>
        <button class="btn" id="btn-reset" onclick="sendReset()">Send reset link</button>
    </div>

    <a href="{{ route('login') }}" class="back">Back to sign in</a>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, sendPasswordResetEmail } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const auth = getAuth(initializeApp({ apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U", authDomain:"nyumba-d932c.firebaseapp.com", projectId:"nyumba-d932c", storageBucket:"nyumba-d932c.firebasestorage.app", messagingSenderId:"268571108072", appId:"1:268571108072:web:23489e5de76de12e579f5d" }));

window.sendReset = async function() {
    const email=document.getElementById('r-email').value.trim();
    const btn=document.getElementById('btn-reset');
    document.getElementById('err-box').style.display='none';
    document.getElementById('ok-box').style.display='none';
    if(!email){document.getElementById('err-box').textContent='Please enter your email address.';document.getElementById('err-box').style.display='block';return;}
    btn.disabled=true; btn.textContent='Sending...';
    try {
        await sendPasswordResetEmail(auth,email);
        document.getElementById('form-section').style.display='none';
        document.getElementById('ok-box').innerHTML='Reset link sent to <strong>'+email+'</strong>. Check your inbox.';
        document.getElementById('ok-box').style.display='block';
    } catch(e){
        const map={'auth/user-not-found':'No account found with this email address.','auth/invalid-email':'Please enter a valid email address.','auth/too-many-requests':'Too many requests. Please try again later.'};
        document.getElementById('err-box').textContent=map[e.code]||'Something went wrong. Please try again.';
        document.getElementById('err-box').style.display='block';
        btn.disabled=false; btn.textContent='Send reset link';
    }
};
</script>
</body>
</html>