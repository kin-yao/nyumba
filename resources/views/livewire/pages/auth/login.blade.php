<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('components.layouts.auth')] class extends Component {}; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f5f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 2px 16px rgba(0,0,0,.06)}
.logo{display:block;height:40px;object-fit:contain;margin:0 auto 32px}
h1{font-family:'DM Serif Display',serif;font-size:22px;font-weight:400;margin-bottom:4px;text-align:center}
.sub{font-size:13px;color:#8a8880;text-align:center;margin-bottom:24px}
.field{margin-bottom:13px}
label{display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.05em;text-transform:uppercase;margin-bottom:5px}
input{width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,.11);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s;background:#fff}
input:focus{border-color:#1a6b52}
.field-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:5px}
.forgot{font-size:12px;color:#1a6b52;text-decoration:none}
.forgot:hover{text-decoration:underline}
.btn{width:100%;height:42px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s;margin-top:4px}
.btn:hover{background:#155c45}
.btn:disabled{background:#a0c4b8;cursor:not-allowed}
.btn-google{width:100%;height:42px;background:#fff;border:1px solid rgba(0,0,0,.11);border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;color:#111110;transition:background .2s}
.btn-google:hover{background:#f5f4f0}
.btn-google:disabled{opacity:.6;cursor:not-allowed}
.divider{display:flex;align-items:center;gap:10px;margin:16px 0}
.divider hr{flex:1;border:none;border-top:1px solid rgba(0,0,0,.08)}
.divider span{font-size:12px;color:#c4c2be}
.err{display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 13px;font-size:13px;color:#dc2626;margin-bottom:14px}
.warn{display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:9px 13px;font-size:13px;color:#92400e;margin-bottom:14px}
.footer{text-align:center;font-size:13px;color:#8a8880;margin-top:20px}
.footer a{color:#1a6b52;text-decoration:none;font-weight:500}
.footer a:hover{text-decoration:underline}
</style>

<div class="card">
    <img src="/images/logo.png" alt="Nyumba" class="logo">
    <h1>Sign in</h1>
    <p class="sub">Welcome back</p>

    <div id="err-box" class="err"></div>
    <div id="warn-box" class="warn">
        Please verify your email before signing in.
        <button onclick="resendVerification()" style="background:none;border:none;color:#1a6b52;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;padding:0;margin-left:4px">Resend link</button>
    </div>

    <div class="field">
        <label>Email</label>
        <input type="email" id="e-email" placeholder="you@example.com"
               onkeydown="if(event.key==='Enter')signInEmail()">
    </div>
    <div class="field">
        <div class="field-row">
            <label>Password</label>
            <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
        </div>
        <input type="password" id="e-pass" placeholder="••••••••"
               onkeydown="if(event.key==='Enter')signInEmail()">
    </div>

    <button class="btn" id="btn-email" onclick="signInEmail()">Sign in</button>

    <div class="divider"><hr><span>or</span><hr></div>

    <button class="btn-google" id="btn-google" onclick="signInGoogle()">
        <svg width="16" height="16" viewBox="0 0 18 18" fill="none">
            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
            <path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </button>

    <div class="footer">
        Don't have an account? <a href="{{ route('register.step1') }}">Create one</a>
    </div>
</div>

<script type="module">
import { initializeApp }     from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getAuth, signInWithEmailAndPassword, GoogleAuthProvider, signInWithPopup, sendEmailVerification } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const app = initializeApp({
    apiKey:"AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U",
    authDomain:"nyumba-d932c.firebaseapp.com",
    projectId:"nyumba-d932c",
    storageBucket:"nyumba-d932c.firebasestorage.app",
    messagingSenderId:"268571108072",
    appId:"1:268571108072:web:23489e5de76de12e579f5d"
});
const auth  = getAuth(app);
const gp    = new GoogleAuthProvider();
let fbUser  = null;

const ERR = {
    // Email / password
    'auth/email-already-in-use':   'An account with this email already exists. Please sign in instead.',
    'auth/invalid-email':          'Please enter a valid email address.',
    'auth/missing-email':          'Please enter your email address.',
    'auth/missing-password':       'Please enter your password.',
    'auth/weak-password':          'Password must be at least 8 characters.',
    'auth/password-does-not-meet-requirements': 'Password is too weak. Use at least 8 characters with letters and numbers.',
    'auth/wrong-password':         'Incorrect password.',
    'auth/invalid-credential':     'Incorrect email or password.',
    'auth/invalid-login-credentials': 'Incorrect email or password.',
    'auth/user-not-found':         'No account found with this email address.',
    'auth/user-disabled':          'This account has been disabled. Please contact support.',
    'auth/operation-not-allowed':  'This sign-in method is currently unavailable. Please try Google or contact support.',

    // Rate limit / network
    'auth/too-many-requests':      'Too many attempts. Please wait a few minutes and try again.',
    'auth/network-request-failed': 'Network error. Please check your connection and try again.',
    'auth/timeout':                'The request timed out. Please try again.',
    'auth/quota-exceeded':         'Service is busy right now. Please try again shortly.',

    // Google / popup
    'auth/popup-closed-by-user':   'Sign-in was cancelled.',
    'auth/cancelled-popup-request':'Sign-in was cancelled.',
    'auth/popup-blocked':          'Your browser blocked the popup. Please allow popups for this site and try again.',
    'auth/unauthorized-domain':    'This domain isn’t authorized for sign-in. Please contact support.',
    'auth/account-exists-with-different-credential': 'An account already exists with this email using a different sign-in method.',
    'auth/credential-already-in-use': 'These credentials are already linked to another account.',
    'auth/operation-not-supported-in-this-environment': 'Sign-in isn’t supported in this browser. Please try another browser.',
    'auth/web-storage-unsupported':'Your browser has cookies/storage disabled. Please enable them and try again.',

    // Session / tokens
    'auth/requires-recent-login':  'Please sign in again to continue.',
    'auth/user-token-expired':     'Your session has expired. Please sign in again.',
    'auth/invalid-user-token':     'Your session is invalid. Please sign in again.',

    // Email links
    'auth/expired-action-code':    'This link has expired. Please request a new one.',
    'auth/invalid-action-code':    'This link is invalid or has already been used.',

    // Config / internal
    'auth/invalid-api-key':        'Configuration error. Please contact support.',
    'auth/api-key-not-valid':      'Configuration error. Please contact support.',
    'auth/app-deleted':            'Configuration error. Please contact support.',
    'auth/internal-error':         'Something went wrong on our end. Please try again.',
};
function fe(code){
    if (code && ERR[code]) return ERR[code];
    if (code) console.warn('Unhandled auth error code:', code);
    return 'Something went wrong. Please try again, or contact support if it continues.';
}
function csrf(){ return document.querySelector('meta[name="csrf-token"]').content; }

function showErr(m){
    const e=document.getElementById('err-box');
    e.textContent=m; e.style.display='block';
    document.getElementById('warn-box').style.display='none';
}
function clearMsg(){
    document.getElementById('err-box').style.display='none';
    document.getElementById('warn-box').style.display='none';
}

async function callVerify(token, provider, intent, extra={}){
    let r;
    try {
        r = await fetch('/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf() },
            body: JSON.stringify({ id_token:token, provider, intent, ...extra }),
        });
    } catch {
        return { error: 'Network error contacting the server. Please try again.' };
    }
    let data = {};
    try { data = await r.json(); } catch {}
    if (!r.ok && !data.error) {
        data.error = r.status === 419
            ? 'Your session expired. Please refresh the page and try again.'
            : 'Server error (' + r.status + '). Please try again.';
    }
    return data;
}

window.signInEmail = async function() {
    const email = document.getElementById('e-email').value.trim();
    const pass  = document.getElementById('e-pass').value;
    const btn   = document.getElementById('btn-email');
    clearMsg();
    if (!email || !pass) { showErr('Please enter your email and password.'); return; }
    btn.disabled=true; btn.textContent='Signing in...';
    try {
        const cred = await signInWithEmailAndPassword(auth, email, pass);
        fbUser     = cred.user;
        const token = await cred.user.getIdToken();
        const data  = await callVerify(token, 'email', 'login');
        if (data.redirect) { window.location.href = data.redirect; return; }
        if (data.error === 'verify_email') {
            document.getElementById('warn-box').style.display = 'block';
        } else {
            showErr(data.error || 'Sign in failed.');
        }
        btn.disabled=false; btn.textContent='Sign in';
    } catch(e) {
        showErr(fe(e.code));
        btn.disabled=false; btn.textContent='Sign in';
    }
};

window.resendVerification = async function() {
    if (!fbUser) { showErr('Please sign in first to resend the verification email.'); return; }
    try {
        await sendEmailVerification(fbUser, { url: window.location.origin + '/auth/verified-callback' });
        document.querySelector('#warn-box button').textContent = 'Sent!';
    } catch(e) {
        showErr('Failed to send verification email. Please try again.');
    }
};

window.signInGoogle = async function() {
    const btn = document.getElementById('btn-google');
    clearMsg(); btn.disabled=true; btn.textContent='Signing in...';
    try {
        const result = await signInWithPopup(auth, gp);
        const token  = await result.user.getIdToken();
        const data   = await callVerify(token, 'google', 'login');
        if (data.redirect) { window.location.href = data.redirect; return; }
        showErr(data.error || 'No account found. Please register first.');
        btn.disabled=false;
        btn.innerHTML=`<svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/><path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg> Continue with Google`;
    } catch(e) {
        showErr(fe(e.code));
        btn.disabled=false;
        btn.innerHTML=`<svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/><path d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg> Continue with Google`;
    }
};
</script>