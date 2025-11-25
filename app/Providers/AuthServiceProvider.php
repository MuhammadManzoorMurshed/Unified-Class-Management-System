<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Classes;
use App\Policies\ClassPolicy;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Classes::class => \App\Policies\ClassPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('class-manage', [ClassPolicy::class, 'manage']);
    }
}