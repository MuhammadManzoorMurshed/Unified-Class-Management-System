<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Logout Route (Correct)
|--------------------------------------------------------------------------
*/
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('web');

/*
|--------------------------------------------------------------------------
| Public Auth Views
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/verify-otp', 'auth.verify-otp')->name('verify-otp');
});

/*
|--------------------------------------------------------------------------
| PROTECTED (AUTH REQUIRED) APP ROUTES
|--------------------------------------------------------------------------
*/
// Route::middleware(['auth:web'])->group(function () {

//     Route::view('/dashboard', 'dashboard')->name('dashboard');

//     // My Classes
//     Route::view('/classes', 'classes.index')->name('classes.index');

//     // Class Workspace (dynamic class page)
//     Route::view('/classes/{id}', 'classes.show')->name('classes.show');
// });

// PROTECTED (AUTH REQUIRED) APP ROUTES
// আপাতত এখানে auth:web সরিয়ে ফেলি, কারণ আমরা JWT/token দিয়ে গার্ড করছি ফ্রন্টএন্ডে
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/classes', 'classes.index')->name('classes.index');
Route::view('/classes/{id}', 'classes.show')->name('classes.show');