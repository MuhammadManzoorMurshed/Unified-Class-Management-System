@extends('layouts.auth')

@section('title', 'Login')
@section('page-id', 'auth.login')

@section('content')
<div class="border p-5 m-5 rounded-lg border-gray-800">
    <div class="flex flex-col justify-center items-center">
        <h1 class="font-bold text-xl mb-1 text-indigo-500">Sign in</h1>
        <p class="font-semibold text-[10px] text-indigo-300 mb-5">
            Use your Admin / Teacher / Student credentials
        </p>
    </div>

    <div id="login-error" class="hidden mb-3 text-xs text-red-400"></div>

    <form id="login-form" class="space-y-4">
        <div>
            <label class="block text-xs font-medium mb-[6px]">Email</label>
            <input type="email" name="email"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="teacher@ucms.edu" required>
        </div>

        <div>
            <label class="block text-xs font-medium mb-[6px]">Password</label>

            <div class="relative">
                <input type="password" name="password" id="password-input"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="••••••••" required>

                {{-- Eye Button --}}
                <button type="button" id="toggle-password"
                    class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-indigo-400 text-xs">
                    👁️‍🗨️
                </button>
            </div>
        </div>


        <button type="submit"
            class="w-1/2 mx-auto mt-2 bg-indigo-500 hover:bg-indigo-600 text-xs font-semibold py-2.5 rounded-lg flex items-center justify-center">
            <span id="login-btn-text">Sign in</span>
            <span id="login-btn-spinner" class="hidden ml-2 text-[11px]">...</span>
        </button>
    </form>
</div>

<div class="mt-4 mb-3 text-xs text-center text-indigo-200">
    Student? Not regestered?
    <a href="{{ route('register') }}" class="font-semibold hover:text-indigo-100 text-[13px]">
        Create A New Account
    </a>
</div>
@endsection