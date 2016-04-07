<?php

namespace App\Uc;

use App\Uc\UcGuard;
use Auth;
use Illuminate\Support\ServiceProvider;

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
        Auth::extend('uc', function ($app, $name, $config) {

            $provider_config = $app['config']['auth.providers.' . $config['provider']];

            $uc_hash_storage = new UcHashStorage($app['db']->connection());

            $guard = new UcGuard(
                new UcUserProvider($app['hash'], $provider_config['model']),
                $this->app['request'],
                $uc_hash_storage
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
