<?php

namespace App\Guards;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Guards\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Server\ResourceServer;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;

class CachedTokenGuard extends TokenGuard
{
    /**
     * Create a new CachedTokenGuard instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Laravel\Passport\TokenRepository  $tokens
     * @param  \Laravel\Passport\ClientRepository  $clients
     * @param  \Illuminate\Contracts\Encryption\Encrypter  $encrypter
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(
        ResourceServer $server,
                       $provider,
        TokenRepository $tokens,
        ClientRepository $clients,
        Encrypter $encrypter,
        Request $request
    ) {
        parent::__construct($server, $provider, $tokens, $clients, $encrypter, $request);
    }

    /**
     * Get the currently authenticated user based on the token, using cache.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        // Retrieve the Bearer token from the request
        $token = $this->request->bearerToken();
        // If no token is provided, return null
        if (!$token) {
            return null;
        }
        $cacheKey = 'auth_user_'.substr($token,0,90);
        // Cache the user for the token
        return Cache::remember($cacheKey, 3600, function () {
            return parent::user(); // Call the original TokenGuard user method
        });
    }
}
