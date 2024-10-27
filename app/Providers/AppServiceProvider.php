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
        Auth::extend('passport', function ($app, $name, array $config) {
            // First create the regular Eloquent user provider
            $userProvider = Auth::createUserProvider($config['provider'] ?? null);

            // Then wrap it in PassportUserProvider
            $passportProvider = new PassportUserProvider(
                $userProvider,
                $app['config']['auth.providers.'.$config['provider'].'.model']
            );

            return new CachedTokenGuard(
                $app->make(ResourceServer::class),
                $passportProvider,
                $app->make('Laravel\Passport\TokenRepository'),
                $app->make('Laravel\Passport\ClientRepository'),
                $app->make('Illuminate\Contracts\Encryption\Encrypter'),
                $app['request']
            );
        });
    }
}
