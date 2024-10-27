<?php

namespace App\Providers;

use App\Guards\CachedTokenGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Guards\TokenGuard;
use League\OAuth2\Server\ResourceServer;
use Laravel\Passport\PassportUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
