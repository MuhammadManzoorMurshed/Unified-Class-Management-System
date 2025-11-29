<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Only consider session-based `web` guard for redirecting guests.
        // Prevents JWT/API-only authentication from causing a redirect to the web register/login pages.
        if (Auth::guard('web')->check()) {
            // Prefer dashboard route for this app shell
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
