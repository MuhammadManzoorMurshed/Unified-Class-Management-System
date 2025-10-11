<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Ensure the framework uses the same guard for Gate/Policy resolution.
        $useGuard = $guard ?: 'api';
        Auth::shouldUse($useGuard);

        // Minimal auth check placeholder. In production, use auth middleware or JWT middleware.
        if (Auth::guard($useGuard)->guest()) {
            return Response::json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
