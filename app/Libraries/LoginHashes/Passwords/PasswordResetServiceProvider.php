<?php

namespace App\Libraries\LoginHashes\Passwords;

use Illuminate\Support\ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();
    }

    /**
     * Register the password broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        // Illuminate\Auth\Passwords\PasswordBrokerManager
        // App\Libraries\LoginHashes\Passwords\PasswordBrokerManager
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });

        $this->app->alias(null,'Illuminate\Auth\Passwords\PasswordBrokerManager');
        $this->app->alias(null,'Illuminate\Contracts\Auth\PasswordBrokerFactory');
        $this->app->alias(null,'Illuminate\Auth\Passwords\PasswordBroker');
        $this->app->alias(null,'Illuminate\Contracts\Auth\PasswordBroker');


        $this->app->alias('auth.password','App\Libraries\LoginHashes\Passwords\PasswordBrokerManager');
        $this->app->alias('auth.password','Illuminate\Contracts\Auth\PasswordBrokerFactory');
        $this->app->alias('auth.password.broker','App\Libraries\LoginHashes\Passwords\PasswordBroker');
        $this->app->alias('auth.password.broker','App\Libraries\LoginHashes\Contracts\PasswordBroker');

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth.password', 'auth.password.broker'];
    }
}
