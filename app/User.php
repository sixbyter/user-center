<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Libraries\LoginHashes\Passwords\CanResetPasswordAdd;

class User extends Authenticatable
{

    use CanResetPasswordAdd;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tel', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $incrementing = false;
}

