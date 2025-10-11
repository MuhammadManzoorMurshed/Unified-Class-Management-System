<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Vendor FortifyServiceProvider কে unregister করে দিচ্ছি
        $this->app->register(\Laravel\Fortify\FortifyServiceProvider::class, false);
    }

    public function boot()
    {
        // 🔹 Step 1: Ignore all default Fortify routes
        Fortify::ignoreRoutes();

        // 🔹 Step 2: Bind Fortify Actions
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(\App\Actions\Fortify\UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(\App\Actions\Fortify\UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(\App\Actions\Fortify\ResetUserPassword::class);

        // 🔹 Step 3: Rate Limiter
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        // 🔹 Step 4: Optional custom email verify response
        Fortify::verifyEmailView(function () {
            return response()->json(['message' => 'Email verified successfully.'], 200);
        });
    }
}