<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth('api')->user();

        // যদি লগইন করা না থাকে
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // যদি role permission না মেলে
        if (!in_array($user->role->role_name, $roles)) {
            return response()->json([
                'message' => 'Insufficient permissions.',
                'required_roles' => $roles,
                'user_role' => $user->role->role_name
            ], 403);
        }

        return $next($request);
    }
}