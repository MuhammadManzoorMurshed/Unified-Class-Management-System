<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        // 🔹 ইনপুট যাচাই
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users'],
            'password' => $this->passwordRules(),
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ])->validate();

        // 🔹 নতুন ইউজার তৈরি
        $user = User::create([
            'role_id'  => $input['role_id'],
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'phone'    => $input['phone'] ?? null,
            'avatar'   => null,
            'status'   => 'active',
        ]);

        // 🔹 OTP ও Token তৈরি ও সংরক্ষণ
        $otp = rand(100000, 999999);
        $token = Str::uuid();

        EmailVerification::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // 🔹 OTP ইমেইলে পাঠানো (Mailtrap dev stage)
        Mail::raw("Your UCMS OTP is: {$otp} (valid for 15 minutes)", function ($msg) use ($user) {
            $msg->to($user->email)->subject('UCMS Email Verification');
        });

        return $user;
    }
}
