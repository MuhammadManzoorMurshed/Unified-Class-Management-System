<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\UserController;

// 🔹 Fortify email verification route
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();
//     return response()->json(['message' => 'Email verified successfully.']);
// })->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// User রিসোর্স API রাউট
Route::get('user/{id}', [UserController::class, 'show']);

// Class রিসোর্স API রাউট
Route::get('class/{id}', [ClassController::class, 'show']);

// 🔹 OTP-based Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::get('/verify-token/{token}', [AuthController::class, 'verifyToken']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/resend-verification-link', [AuthController::class, 'resendVerificationLink']);
    Route::post('/request-password-otp', [AuthController::class, 'requestPasswordOtp']);
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});


// 🔹 Profile update (protected route)
// ⛔️ NOTE: এই রুটটা auth গ্রুপের বাইরে রাখো
Route::prefix('v1')->middleware('auth:api')->group(function () {
    // 🔹 শুধুমাত্র Admin ও Teacher নতুন ক্লাস তৈরি করতে পারবে
    Route::post('/classes', [ClassController::class, 'store'])
        ->middleware('role:Admin,Teacher');

    // 🔹 Teacher (নিজের ক্লাস) বা Admin ক্লাস আপডেট করতে পারবে
    Route::put('/classes/{id}', [ClassController::class, 'update'])
        ->middleware('role:Admin,Teacher');

    // 🔹 শুধুমাত্র Admin ক্লাস মুছে ফেলতে পারবে
    Route::delete('/classes/{id}', [ClassController::class, 'destroy'])
        ->middleware('role:Admin');

    Route::get('/me', [ProfileController::class, 'viewProfile']);
    Route::put('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth:api'])->group(function () {
        // POST মেথডের জন্য রুট যোগ করুন
        Route::post('classes/join', [ClassController::class, 'join']);
    });
    Route::delete('classes/{classId}/members/{userId}', [ClassController::class, 'removeMember']);
});