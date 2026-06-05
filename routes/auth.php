<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Livewire\Volt\Volt;

// Only logout remains here. All other auth routes are in routes/web.php.
Route::middleware('auth')->post('logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware('guest')->group(function () {
    Volt::route('/login', 'pages.auth.login')->name('login');  // ← add this line
    Route::get('/register/step1', [App\Http\Controllers\Auth\RegisterController::class, 'showStep1'])->name('register.step1');
    Route::get('/register/step2', [App\Http\Controllers\Auth\RegisterController::class, 'showStep2'])->name('register.step2');
    Route::get('/register/step3', [App\Http\Controllers\Auth\RegisterController::class, 'showStep3'])->name('register.step3');
    Route::post('/register/step3', [App\Http\Controllers\Auth\RegisterController::class, 'processStep3'])->name('register.step3.post');
    Route::get('/register/step4', [App\Http\Controllers\Auth\RegisterController::class, 'showStep4'])->name('register.step4');
    Route::post('/register/step4', [App\Http\Controllers\Auth\RegisterController::class, 'processStep4'])->name('register.step4.post');
});