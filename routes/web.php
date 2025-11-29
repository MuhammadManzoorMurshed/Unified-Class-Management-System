<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Logout Route
|--------------------------------------------------------------------------
*/
Route::post('/logout', function (Request $request) {
    // শুধু web guard এর session logout
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('web');

/*
|--------------------------------------------------------------------------
| Public Auth Views (guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/verify-otp', 'auth.verify-otp')->name('verify-otp');
});

/*
|--------------------------------------------------------------------------
| APP SHELL ROUTES (SPA, NO auth:web)
|--------------------------------------------------------------------------
| এগুলো শুধু Blade শেল লোড করবে।
| আসল ডেটা আসবে /api/v1/... থেকে (JWT token সহ).
*/
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/classes', 'classes.index')->name('classes.index');
Route::view('/classes/{id}', 'classes.show')->name('classes.show');