<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustProxies
{
    public function handle(Request $request, Closure $next)
    {
        // Minimal pass-through proxy middleware. If you need advanced proxy handling,
        // install and configure fideloper/proxy or the framework's proxy trust features.
        return $next($request);
    }
}
