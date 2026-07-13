<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Only logout remains here. All other auth routes are in routes/web.php.
Route::middleware('auth')->post('logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');