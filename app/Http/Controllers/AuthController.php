<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // ======================================================
    // 🔹 1️⃣ REGISTER USER + SEND INITIAL OTP
    // ======================================================
    public function register(Request $req)
    {
        $req->validate([
            'name'      => 'required|string|max:120',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed|min:8',
            'role_id'   => 'required|exists:roles,id',
            'phone'     => 'nullable|string|max:20',
        ]);

        // 🧩 Create user
        $user = User::create([
            'name'     => $req->name,
            'email'    => $req->email,
            'password' => Hash::make($req->password),
            'role_id'  => $req->role_id,
            'phone'    => $req->phone,
            'status'   => 'active',
        ]);

        // 🧩 Generate OTP + Token
        $otp = rand(100000, 999999);
        $token = Str::uuid();

        // Save verification record
        EmailVerification::create([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'token'      => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email
        Mail::raw("Your UCMS OTP is: $otp (valid for 15 minutes)", function ($msg) use ($user) {
            $msg->to($user->email)->subject('UCMS Email Verification');
        });

        return response()->json([
            'message' => 'Registered successfully. OTP sent to your email.',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            // Only for testing purpose
            'otp' => $otp,
            'token' => $token,
            'expires_in_minutes' => 15
        ], 201);
    }

    // ======================================================
    // 🔹 2️⃣ VERIFY OTP (User manually enters OTP)
    // ======================================================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'token' => 'required|uuid',
            'otp'   => 'required|digits:6',
        ]);

        $record = EmailVerification::where('token', $request->token)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid verification token.'], 404);
        }

        if ($record->is_used) {
            return response()->json(['message' => 'This OTP has already been used.'], 400);
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            return response()->json(['message' => 'This OTP has expired. Please request a new one.'], 400);
        }

        if ($record->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP code. Please check and try again.'], 400);
        }

        // ✅ Success
        $user = $record->user;
        $user->email_verified_at = now();
        $user->save();
        $record->update(['is_used' => true]);

        return response()->json([
            'message' => 'Email verified successfully via OTP!',
            'verified_user' => [
                'id' => $user->id,
                'email' => $user->email,
                'verified_at' => $user->email_verified_at
            ]
        ], 200);
    }

    // ======================================================
    // 🔹 3️⃣ VERIFY TOKEN (User clicks email link)
    // ======================================================
    public function verifyToken($token)
    {
        $record = EmailVerification::where('token', $token)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid verification link or token.'], 404);
        }

        if ($record->is_used) {
            return response()->json(['message' => 'This verification link has already been used.'], 400);
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            return response()->json(['message' => 'This verification link has expired. Please request a new one.'], 400);
        }

        $user = $record->user;
        $user->email_verified_at = now();
        $user->save();
        $record->update(['is_used' => true]);

        return response()->json([
            'message' => 'Email verified successfully via verification link!',
            'verified_user' => [
                'id' => $user->id,
                'email' => $user->email,
                'verified_at' => $user->email_verified_at
            ]
        ], 200);
    }

    // ======================================================
    // 🔹 4️⃣ RESEND OTP (Manual re-verification)
    // ======================================================
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found with this email address.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        // Invalidate old unused OTPs
        EmailVerification::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otp = rand(100000, 999999);
        $token = Str::uuid();

        EmailVerification::create([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'token'      => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw("Your new UCMS OTP is: $otp (valid for 15 minutes)", function ($msg) use ($user) {
            $msg->to($user->email)->subject('UCMS OTP Resend');
        });

        return response()->json([
            'message' => 'A new OTP has been sent successfully!',
            'token'   => $token,
            'otp'     => $otp, // For testing only
            'expires_in_minutes' => 15
        ], 200);
    }

    // ======================================================
    // 🔹 5️⃣ RESEND VERIFICATION LINK (for link-based flow)
    // ======================================================
    public function resendVerificationLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found with this email address.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        // Invalidate old tokens
        EmailVerification::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $token = Str::uuid();

        EmailVerification::create([
            'user_id'    => $user->id,
            'otp'        => rand(100000, 999999),
            'token'      => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        // Create verification link
        $verificationUrl = url("/api/auth/verify-token/{$token}");

        Mail::raw("Click the link below to verify your email:\n\n{$verificationUrl}\n\nThis link will expire in 30 minutes.", function ($msg) use ($user) {
            $msg->to($user->email)->subject('UCMS Email Verification Link');
        });

        return response()->json([
            'message' => 'A new verification link has been sent successfully!',
            'verification_url' => $verificationUrl, // For testing only
            'expires_in_minutes' => 30
        ], 200);
    }

    // 🔹 4️⃣ Request Password Reset OTP (purpose = password_reset)
    public function requestPasswordOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $otp = rand(100000, 999999);
        $token = Str::uuid();
        $user = User::where('email', $request->email)->first();

        EmailVerification::create([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'token'      => $token,
            'expires_at' => now()->addMinutes(15),
            'purpose'    => 'password_reset',
        ]);

        Mail::raw("Your UCMS password reset OTP is: $otp (valid for 15 minutes)", function ($msg) use ($user) {
            $msg->to($user->email)->subject('UCMS Password Reset OTP');
        });

        return response()->json([
            'message' => 'Password reset OTP sent successfully!',
            'otp'     => $otp,   // টেস্টের জন্য
            'token'   => $token
        ]);

        return response()->json([
            'message' => 'Password reset OTP sent successfully!',
            'otp'     => $otp,   // ✅ টেস্টিং এর জন্য এখানে দেখানো হচ্ছে
            'token'   => $token
        ]);
    }

    // 🔹 5️⃣ Verify Password Reset OTP and Update Password
    public function verifyPasswordOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|digits:6',
            'password' => 'required|confirmed|min:8',
        ]);

        $record = EmailVerification::where('purpose', 'password_reset')
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        $record->update(['is_used' => true]);

        return response()->json(['message' => 'Password reset successfully!']);
    }

    // 🔹 User Login → JWT Token সহ রেসপন্স
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Use the JWTAuth facade to create a token using credentials.
        // This avoids relying on the default auth guard configuration.
        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Retrieve the authenticated user via JWTAuth
        /** @var \App\Models\User $user */
        $user = JWTAuth::user();

        // ইমেইল ভেরিফিকেশন চেক
        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    // 🔹 Update Profile
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = JWTAuth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => '👋 Logged out successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => JWTAuth::user()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token refresh failed'], 401);
        }
    }
}