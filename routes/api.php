<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\examController;
use App\Http\Controllers\MarksController;
use App\Http\Controllers\ClassChatController;
use App\Http\Controllers\DashboardController;

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


// Profile update (protected route)
// NOTE: এই রুটটা auth গ্রুপের বাইরে রাখো
Route::prefix('v1')->middleware('auth:api')->group(function () {
    // ---------- CLASS ROUTES ----------
    // My Classes (সবার জন্য, শুধু logged-in হলেই হবে)
    Route::get('/my-classes', [ClassController::class, 'myClasses']);
    
    // 🔹 শুধুমাত্র Admin ও Teacher নতুন ক্লাস তৈরি করতে পারবে
    Route::post('/classes', [ClassController::class, 'store'])
        ->middleware('role:Admin,Teacher');

    // 🔹 Teacher (নিজের ক্লাস) বা Admin ক্লাস আপডেট করতে পারবে
    Route::put('/classes/{id}', [ClassController::class, 'update'])
        ->middleware('role:Admin,Teacher');

    // 🔹 শুধুমাত্র Admin ক্লাস মুছে ফেলতে পারবে
    Route::delete('/classes/{id}', [ClassController::class, 'destroy'])
        ->middleware('role:Admin,Teacher');

    // Join / Remove member
    Route::post('/classes/join', [ClassController::class, 'join']);
    Route::delete('/classes/{classId}/members/{userId}', [ClassController::class, 'removeMember']);
    

    // ---------- PROFILE ROUTES ----------
    Route::get('/me', [ProfileController::class, 'viewProfile']);
    Route::put('/update-profile', [ProfileController::class, 'updateProfile']);

    // ---------- LOGOUT ROUTE ----------
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---------- ASSIGNMENT ROUTEs ----------
    // Assignment list (Teacher/Student)
    Route::get('/classes/{class}/assignments', [AssignmentController::class, 'index']);

    // Assignment create (Teacher/Admin only)
    Route::post('/classes/{class}/assignments', [AssignmentController::class, 'store'])
        ->middleware('role:Admin,Teacher');

    // ----------ASSIGNMENT SUBMISSION ROUTES ----------
    // Student submission
    Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])
        ->middleware('role:Student');

    // Student: view own submission
    Route::get('/assignments/{assignment}/my-submission', [SubmissionController::class, 'showMySubmission'])
        ->middleware('role:Student');

    // Teacher/Admin: view all submissions
    Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'index'])
        ->middleware('role:Admin,Teacher');

    // Teacher/Admin → submission এর marks আপডেট
    Route::post('/submissions/{submission}/marks', [SubmissionController::class, 'updateMarks']);

    Route::get('/submissions/{submission}/file', [SubmissionController::class, 'viewFile'])
        ->name('submissions.file');

    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'downloadFile'])
        ->name('submissions.download');

    // ----------ATTENDANCE ROUTES ----------
    // 🔹 Mark attendance (Teacher/Admin)
    Route::post('/classes/{class}/attendance', [AttendanceController::class, 'mark'])
        ->middleware('role:Admin,Teacher');

    // 🔹 Student → My attendance
    Route::get('/classes/{class}/my-attendance', [AttendanceController::class, 'myAttendance'])
        ->middleware('role:Student');

    // 🔹 Teacher/Admin → Class attendance list
    Route::get('/classes/{class}/attendance', [AttendanceController::class, 'classAttendance'])
        ->middleware('role:Admin,Teacher');

    // ----------eXAMX & MARKS ----------
    // Exams
    Route::get('/classes/{class}/exams', [ExamController::class, 'index']);
    Route::post('/classes/{class}/exams', [ExamController::class, 'store'])
        ->middleware('role:Admin,Teacher');

    // Marks
    Route::post('/exams/{exam}/marks', [MarksController::class, 'store'])
        ->middleware('role:Admin,Teacher');

    Route::get('/classes/{class}/my-marks', [MarksController::class, 'myMarks'])
        ->middleware('role:Student');

    Route::get('/exams/{exam}/marks', [MarksController::class, 'examMarks'])
        ->middleware('role:Admin,Teacher');

    // Route::middleware(['auth:api'])->group(function () {
    //     // POST মেথডের জন্য রুট যোগ করুন
    //     Route::post('classes/join', [ClassController::class, 'join']);
    // });
    // Route::delete('classes/{classId}/members/{userId}', [ClassController::class, 'removeMember']);

    // ---------- CLASS CHATS ROUTES ----------
    Route::get('/classes/{class}/chats', [ClassChatController::class, 'index']);
    Route::post('/classes/{class}/chats', [ClassChatController::class, 'store']);

    Route::get('/dashboard', [DashboardController::class, 'summary']);
});