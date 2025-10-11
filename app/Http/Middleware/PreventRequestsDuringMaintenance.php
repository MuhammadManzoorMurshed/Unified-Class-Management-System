<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventRequestsDuringMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        // Minimal maintenance check — you can integrate with the framework's maintenance mode.
        return $next($request);
    }
}
