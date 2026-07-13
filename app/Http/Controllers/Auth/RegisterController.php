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
}