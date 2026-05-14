<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        if (app()->isLocal()) {
            // cURL's async DNS resolver (c-ares) cannot resolve hostnames on this machine
            // because outbound UDP port 53 is blocked. PHP's own stream sockets work fine.
            // Override Socialite's HTTP client to use Guzzle's StreamHandler, which resolves
            // DNS through PHP's built-in resolver instead of c-ares.
            Socialite::extend('google', function () {
                $stack = HandlerStack::create(new StreamHandler());
                return Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class,
                    config('services.google')
                )->setHttpClient(new Client(['handler' => $stack]));
            });
        }
    }
}
