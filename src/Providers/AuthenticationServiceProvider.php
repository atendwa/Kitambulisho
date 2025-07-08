<?php

namespace Atendwa\Kitambulisho\Providers;

use Atendwa\Kitambulisho\Listeners\LoginListener;
use Atendwa\Kitambulisho\Services\DatabaseAuthenticator;
use Atendwa\Kitambulisho\Services\MasqueradeAuthenticator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/authentication.php', 'authentication');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'authentication');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->publishes([
            __DIR__ . '/../../config/authentication.php' => config_path('authentication.php'),
        ], 'config');

        Event::subscribe(LoginListener::class);

        $this->app->singleton('authenticator.drivers', fn () => [
            'masquerade' => fn () => new MasqueradeAuthenticator(),
            'database' => fn () => new DatabaseAuthenticator(),
        ]);
    }
}
