@extends('layouts.auth')

@section('title', 'Verify OTP')
@section('page-id', 'auth.verify-otp')

@section('content')
<div class="border p-5 m-5 rounded-lg border-gray-800">
    <div class="flex flex-col justify-center items-center">
        <h1 class="font-bold text-xl mb-1 text-indigo-500">Verify your email</h1>
        <p class="font-semibold text-[10px] text-indigo-300 mb-1">
            We have sent a verification code to
        </p>
        <p id="otp-email-display" class="font-semibold text-[10px] text-indigo-200 mb-4">
            <!-- JS will fill -->
        </p>
    </div>

    <div id="otp-error" class="hidden mb-3 text-xs text-red-400"></div>
    <div id="otp-success" class="hidden mb-3 text-xs text-emerald-400"></div>

    <form id="otp-form" class="space-y-4">
        <div>
            <label class="block text-xs font-medium mb-[6px]">Verification Code (OTP)</label>
            <input type="text" name="otp"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter the code" required>
        </div>

        <button type="submit"
            class="w-1/2 mx-auto mt-2 bg-indigo-500 hover:bg-indigo-600 text-xs font-semibold py-2.5 rounded-lg flex items-center justify-center">
            <span id="otp-btn-text">Verify</span>
            <span id="otp-btn-spinner" class="hidden ml-2 text-[11px]">Processing...</span>
        </button>
    </form>

    <div class="mt-4 text-[10px] text-center text-indigo-200">
        Didn't receive the code?
        <button id="btn-resend-otp" class="font-semibold underline hover:text-indigo-100">
            Resend OTP
        </button>
    </div>

    <div class="mt-3 text-[10px] text-center text-slate-400">
        <a href="{{ route('login') }}" class="underline hover:text-indigo-200">
            Back to Login
        </a>
    </div>
</div>
@endsection