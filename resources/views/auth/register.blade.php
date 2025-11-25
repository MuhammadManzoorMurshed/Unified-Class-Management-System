@extends('layouts.auth')

@section('title', 'Register')
@section('page-id', 'auth.register')

@section('content')
<div class="border p-5 m-5 rounded-lg border-gray-800">
    <div class="flex flex-col justify-center items-center">
        <h1 class="font-bold text-xl mb-1 text-indigo-500">Create Student Account</h1>
        <p class="font-semibold text-[10px] text-indigo-300 mb-5">
            Register as a student using your email
        </p>
    </div>

    <div id="register-error" class="hidden mb-3 text-xs text-red-400"></div>
    <div id="register-success" class="hidden mb-3 text-xs text-emerald-400"></div>

    <form id="register-form" class="space-y-4">
        <div>
            <label class="block text-xs font-medium mb-[6px]">Full Name</label>
            <input type="text" name="name"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Student Name" required>
        </div>

        <div>
            <label class="block text-xs font-medium mb-[6px]">Email</label>
            <input type="email" name="email"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="student@example.com" required>
        </div>

        <div>
            <label class="block text-xs font-medium mb-[6px]">Password</label>
            <div class="relative">
                <input type="password" name="password" id="reg-password-input"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="••••••••" required>
                <button type="button" id="reg-toggle-password"
                    class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-indigo-400 text-xs">
                    👁️‍🗨️
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium mb-[6px]">Confirm Password</label>
            <input type="password" name="password_confirmation"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="••••••••" required>
        </div>

        <button type="submit"
            class="w-1/2 mx-auto mt-2 bg-indigo-500 hover:bg-indigo-600 text-xs font-semibold py-2.5 rounded-lg flex items-center justify-center">
            <span id="register-btn-text">Register</span>
            <span id="register-btn-spinner" class="hidden ml-2 text-[11px]">Processing...</span>
        </button>
    </form>
</div>

<div class="mt-4 mb-3 text-[10px] text-xs text-center text-indigo-200">
    Already have an account?
    <a href="{{ route('login') }}" class="font-semibold hover:text-indigo-100 text-[13px]">
        Sign in
    </a>
</div>
@endsection