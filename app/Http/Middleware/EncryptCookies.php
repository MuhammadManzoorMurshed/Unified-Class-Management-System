<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EncryptCookies
{
    public function handle(Request $request, Closure $next)
    {
        // Minimal placeholder — use framework's EncryptCookies for real encryption.
        return $next($request);
    }
}
