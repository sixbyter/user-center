<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::creating(function ($user) {
            $id       = $user->getConnection()->table('user_ids')->insertGetId([]);
            $user->id = $id;
        });

        Validator::extend('account', function ($attribute, $value, $parameters) {
            if (is_tel($value)) {
                return true;
            }
            if (is_email($value)) {
                return true;
            }
            return false;
        });

        Validator::extend('account_unique', function ($attribute, $value, $parameters) {
            $type = get_acount_type($value);
            if ($type === null) {
                return false;
            }

            if (empty(User::where($type, $value)->first())) {
                return true;
            }
            return false;

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
