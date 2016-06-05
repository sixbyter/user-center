<?php

namespace App\Libraries\SignToken;

use Auth;
use Illuminate\Support\ServiceProvider;
use App\Libraries\SignToken\Contracts\AppRepository as AppRepositoryContracts;

class SignTokenServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AppRepositoryContracts::class, function ($app) {
            // $repository = new AppRepository(new AppArray);
            $repository = new AppRepository(new \App\App);
            return $repository;
        });
    }
}
