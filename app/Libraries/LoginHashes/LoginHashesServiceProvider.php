<?php

namespace App\Libraries\LoginHashes;

use Auth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\ServiceProvider;
use App\Libraries\LoginHashes\LoginHashesGuard;
use App\Libraries\LoginHashes\LoginHashesSessionGuard;
use App\Libraries\LoginHashes\Contracts\LoginHashesRepository;
use App\Libraries\LoginHashes\Contracts\AppRepository as AppRepositoryContracts;

class LoginHashesServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot()
    {
        Auth::extend('login_hashes', function ($app, $name, $config) {

            $guard = $app->make(LoginHashesGuard::class, [
                $app['auth']->createUserProvider($config['provider']),
                $this->app['request'],
            ]);

            $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        Auth::extend('login_hashes_session', function ($app, $name, $config) {

            $provider = $app['auth']->createUserProvider($config['provider']);

            // $guard = new LoginHashesSessionGuard($name, $provider, $app['session.store']);

            $guard = $app->make(LoginHashesSessionGuard::class, [
                $name,
                $provider,
                $app['session.store'],
            ]);

            // When using the remember me functionality of the authentication services we
            // will need to be set the encryption instance of the guard, which allows
            // secure, encrypted cookie values to get generated for those cookies.
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

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
        $this->app->bind(LoginHashesRepository::class, function ($app) {
            return new DatabaseLoginHashesRepository($app['db']->connection());
        });

        $this->app->singleton(AppRepositoryContracts::class, function ($app) {
            $repository = new AppRepository($app['request'], new \App\App);
            $repository->setMyAppId($app['config']['auth']['myapp']['id']);
            return $repository;
        });
    }
}
