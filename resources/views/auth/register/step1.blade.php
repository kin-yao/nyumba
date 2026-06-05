<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create account — Nyumba</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06)}
        .logo{display:block;height:40px;object-fit:contain;margin:0 auto 28px}
        .steps{display:flex;gap:5px;margin-bottom:24px}
        .step{height:2px;border-radius:1px;flex:1}
        .step.active{background:#1a6b52}
        .step.inactive{background:#e5e3de}
        h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:4px}
        .sub{font-size:13px;color:#8a8880;margin-bottom:22px}
        .field{margin-bottom:13px}
        label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}
        input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.11);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#1a6b52}
        .field-err{font-size:12px;color:#dc2626;margin-top:3px}
        .btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s;margin-top:4px}
        .btn:hover{background:#155c45}
        .btn:disabled{background:#a0c4b8;cursor:not-allowed}
        .btn-google{width:100%;height:42px;background:#fff;border:1px solid rgba(0,0,0,.11);border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;color:#111110;transition:background .2s}
        .btn-google:hover{background:#f5f4f0}
        .divider{display:flex;align-items:center;gap:10px;margin:16px 0}
        .divider hr{flex:1;border:none;border-top:1px solid rgba(0,0,0,.08)}
        .divider span{font-size:12px;color:#c4c2be}
        .err{display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 13px;font-size:13px;color:#dc2626;margin-bottom:14px}
        .footer{text-align:center;font-size:13px;color:#8a8880;margin-top:20px}
        .footer a{color:#1a6b52;text-decoration:none;font-weight:500}
    </style>
</head>
<body>
<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <div class="steps">
        <div class="step active"></div>
        <div class="step inactive"></div>
        <div class="step inactive"></div>
        <div class="step inactive"></div>
    </div>
    <h1>Create your account</h1>
    <p class="sub">Step 1 of 4</p>

    <div id="err-box" class="err"></div>

    <div class="field">
        <label>Full name</label>
        <input type="text" id="name" placeholder="John Kamau" autofocus>
        <div class="field-err" id="e-name"></div>
    </div>
    <div class="field">
        <label>Email address</label>
        <input type="email" id="email" placeholder="you@example.com">
        <div class="field-err" id="e-email"></div>
    </div>
    <div class="field">
        <label>Phone number</label>
        <input type="text" id="phone" placeholder="07XX or 254XXXXXXXXX">
        <div class="field-err" id="e-phone"></div>
    </div>
    <div class="field">
        <label>Password</label>
        <input type="password" id="password" placeholder="Min 8 characters">
        <div class="field-err" id="e-password"></div>
    </div>
    <div class="field">
        <label>Confirm password</label>
        <input type="password" id="confirm" placeholder="Repeat password">
        <div class="field-err" id="e-confirm"></div>
    </div>

    <button class="btn" id="btn-create" onclick="createAccount()">Create account</button>

    <div class="divider"><hr><span>or</span><hr></div>

    <button class="btn-google" id="btn-google" onclick="googleSignUp()">
        <svg width="16" height="16" viewBox="0 0 18 18" fill="none">
            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
            <path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </button>

    <div class="footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, createUserWithEmailAndPassword, sendEmailVerification, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const FB = { apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U", authDomain:"nyumba-d932c.firebaseapp.com", projectId:"nyumba-d932c", storageBucket:"nyumba-d932c.firebasestorage.app", messagingSenderId:"268571108072", appId:"1:268571108072:web:23489e5de76de12e579f5d" };
const auth = getAuth(initializeApp(FB));
const gp   = new GoogleAuthProvider();
const CONTINUE_URL = window.location.origin + '/auth/verified-callback';

const ERR = {
    'auth/email-already-in-use':'An account with this email already exists. Please sign in.',
    'auth/weak-password':'Password must be at least 8 characters.',
    'auth/invalid-email':'Please enter a valid email address.',
    'auth/too-many-requests':'Too many attempts. Please try again later.',
    'auth/network-request-failed':'Network error. Please check your connection.',
};
function fe(code){return ERR[code]||'Something went wrong. Please try again.';}
function csrf(){return document.querySelector('meta[name="csrf-token"]').content;}
function showErr(m){const e=document.getElementById('err-box');e.textContent=m;e.style.display='block';}
function clearErr(){document.getElementById('err-box').style.display='none';['name','email','phone','password','confirm'].forEach(f=>{document.getElementById('e-'+f).textContent=''});}

async function callVerify(token,provider,intent,extra={}){
    const r=await fetch('/auth/verify',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},body:JSON.stringify({id_token:token,provider,intent,...extra})});
    return r.json();
}

window.createAccount = async function() {
    clearErr();
    const name=document.getElementById('name').value.trim();
    const email=document.getElementById('email').value.trim();
    const phone=document.getElementById('phone').value.trim();
    const pass=document.getElementById('password').value;
    const confirm=document.getElementById('confirm').value;
    let ok=true;
    if(!name){document.getElementById('e-name').textContent='Required';ok=false;}
    if(!email){document.getElementById('e-email').textContent='Required';ok=false;}
    if(!phone){document.getElementById('e-phone').textContent='Required';ok=false;}
    if(!pass){document.getElementById('e-password').textContent='Required';ok=false;}
    if(pass!==confirm){document.getElementById('e-confirm').textContent='Passwords do not match';ok=false;}
    if(!ok)return;

    const btn=document.getElementById('btn-create');
    btn.disabled=true; btn.textContent='Creating account...';
    try {
        const cred=await createUserWithEmailAndPassword(auth,email,pass);
        await sendEmailVerification(cred.user,{url:CONTINUE_URL});
        const token=await cred.user.getIdToken();
        const d=await callVerify(token,'email','register',{name,phone});
        if(d.redirect){window.location.href=d.redirect;return;}
        showErr(d.error||'Registration failed. Please try again.');
        btn.disabled=false; btn.textContent='Create account';
    } catch(e){
        if(e.code==='auth/email-already-in-use'){document.getElementById('e-email').textContent=fe(e.code);}
        else{showErr(fe(e.code));}
        btn.disabled=false; btn.textContent='Create account';
    }
};

window.googleSignUp = async function() {
    clearErr();
    const btn=document.getElementById('btn-google');
    btn.disabled=true; btn.textContent='Connecting...';
    try {
        const r=await signInWithPopup(auth,gp);
        const t=await r.user.getIdToken();
        const d=await callVerify(t,'google','register');
        if(d.redirect){window.location.href=d.redirect;return;}
        showErr(d.error||'Something went wrong.');
        btn.disabled=false; btn.textContent='Continue with Google';
    } catch(e){showErr(fe(e.code)); btn.disabled=false; btn.textContent='Continue with Google';}
};
</script>
</body>
</html>