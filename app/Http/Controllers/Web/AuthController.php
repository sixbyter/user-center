<?php

namespace App\Http\Controllers\Web;

use App\User;
use Validator;
use App\Http\Controllers\WebController;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Events\Register;

class AuthController extends WebController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $guard = 'web';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        return Validator::make($data, [
            'account'  => 'required|account|account_unique',
            'password' => 'required|min:6|confirmed',
            'nickname' => 'required|max:50',
        ]);

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $account_flag = get_acount_type($data['account']);

        // 创建新用户.
        $new_user = [
            'password'    => bcrypt($data['password']),
            $account_flag => $data['account'],
        ];

        $user = User::create($new_user);

        // 触发一个发邮件的事件
        
        event(new Register($user));

        return $user;
    }
}
