<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // web routes (no leading slash) — matches requests like POST to /register
        'register',
        'login',
        'forgot-password',
        'reset-password',
        // allow API testing endpoints
        'api/*',
        // common variants to ensure matching in different request contexts
        '/register',
        '/api/*',
        // absolute URL variant (matches fullUrlIs)
        'http://127.0.0.1:8000/register',
    ];
}
