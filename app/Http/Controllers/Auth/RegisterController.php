<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showStep1()
    {
        return view('auth.register.step1');
    }

    public function showStep2()
    {
        if (!session('firebase.uid') && !session('reg.email')) {
            return redirect()->route('register.step1');
        }
        return view('auth.register.step2');
    }

    public function showStep3()
    {
        if (!session('firebase.uid')) {
            return redirect()->route('register.step1');
        }

        if (session('firebase.provider') === 'google') {
            if (!session('firebase.phone') && !session('reg.phone')) {
                return redirect()->route('register.phone');
            }
        }

        if (session('firebase.provider') === 'email' && !session('firebase.email_verified')) {
            return redirect()->route('register.step2');
        }

        return view('auth.register.step3');
    }

    public function processStep3(Request $request)
    {
        $request->validate([
            'use_case' => ['required', 'string', 'in:own_rental,property_manager,utility_billing,commercial,mixed'],
        ]);

        session(['reg.use_case' => $request->use_case]);

        return redirect()->route('register.step4');
    }

    public function showStep4()
    {
        if (!session('reg.use_case')) {
            return redirect()->route('register.step3');
        }
        return view('auth.register.step4');
    }

    public function processStep4(Request $request)
    {
        if (!session('firebase.uid')) {
            return redirect()->route('register.step1');
        }

        return app(FirebaseAuthController::class)->createAccount($request);
    }
}