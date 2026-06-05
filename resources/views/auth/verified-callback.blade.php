<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifying — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06);text-align:center}
        .logo{display:block;height:40px;object-fit:contain;margin:0 auto 32px}
        h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:10px}
        p{font-size:13px;color:#8a8880;line-height:1.6}
        .spinner{width:32px;height:32px;border:3px solid #e5e3de;border-top-color:#1a6b52;border-radius:50%;animation:spin 0.8s linear infinite;margin:20px auto}
        @keyframes spin{to{transform:rotate(360deg)}}
        .err{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;font-size:13px;color:#dc2626;margin-top:16px;display:none}
        .link{display:inline-block;margin-top:16px;font-size:13px;color:#1a6b52;text-decoration:none;font-weight:500}
        .link:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <div id="loading">
        <div class="spinner"></div>
        <h1>Verifying your email</h1>
        <p>Please wait a moment...</p>
    </div>
    <div id="success" style="display:none">
        <h1>Email verified</h1>
        <p>Redirecting you to the dashboard...</p>
    </div>
    <div id="error-state" style="display:none">
        <h1>Something went wrong</h1>
        <div class="err" id="err-msg" style="display:block"></div>
        <a href="{{ route('login') }}" class="link">Go to sign in</a>
    </div>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const auth = getAuth(initializeApp({ apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U", authDomain:"nyumba-d932c.firebaseapp.com", projectId:"nyumba-d932c", storageBucket:"nyumba-d932c.firebasestorage.app", messagingSenderId:"268571108072", appId:"1:268571108072:web:23489e5de76de12e579f5d" }));

function csrf(){return document.querySelector('meta[name="csrf-token"]').content;}
function showError(msg){
    document.getElementById('loading').style.display='none';
    document.getElementById('error-state').style.display='block';
    document.getElementById('err-msg').textContent=msg;
}

// Firebase restores auth state and we check verification
onAuthStateChanged(auth, async user => {
    if(!user){
        // User not signed into Firebase on this device
        // They may have verified on another device — redirect to login
        setTimeout(()=>{ window.location.href='{{ route("login") }}'; }, 1500);
        document.getElementById('loading').querySelector('p').textContent='Redirecting to sign in...';
        return;
    }

    try {
        await user.reload();

        if(!user.emailVerified){
            showError('Email not yet verified. Please click the link in your inbox first.');
            return;
        }

        const token = await user.getIdToken(true);
        const res   = await fetch('/auth/mark-verified', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf() },
            body: JSON.stringify({ id_token: token }),
        });
        const data = await res.json();

        if(data.redirect){
            document.getElementById('loading').style.display='none';
            document.getElementById('success').style.display='block';
            setTimeout(()=>{ window.location.href=data.redirect; }, 1200);
        } else {
            showError(data.error||'Something went wrong. Please try signing in.');
        }
    } catch(e){
        showError('Something went wrong. Please try signing in.');
    }
});
</script>
</body>
</html>