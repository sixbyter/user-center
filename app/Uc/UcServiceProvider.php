<?php

namespace App\Uc;

use App\Uc\HashGuard;
use Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\EloquentUserProvider;

class UcServiceProvider extends ServiceProvider
{

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot()
    {
        Auth::extend('hash', function ($app, $name, $config) {

            $provider_config = $app['config']['auth.providers.' . $config['provider']];

            $hash_provider = new DatabaseHashProvider($app['db']->connection());

            $guard = new HashGuard(
                new EloquentUserProvider($app['hash'], $provider_config['model']),
                $this->app['request'],
                $hash_provider
            );

            $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
