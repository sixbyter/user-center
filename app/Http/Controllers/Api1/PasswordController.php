<?php

namespace App\Http\Controllers\Api1;

use App\Libraries\Api\IHelper;
use App\Http\Controllers\ApiController;
use App\Libraries\LoginHashes\Passwords\EmailForgot;
use App\Libraries\LoginHashes\Passwords\TelForgot;
use Auth;
use Illuminate\Http\Request;
use Password;
use Validator;

class PasswordController extends ApiController
{

    use EmailForgot, TelForgot;

    protected $guard = 'api';

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
     */

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:' . $this->getGuard(), ['only' => ['getVerifyOldPassWord', 'postVerifyOldPassWord']]);
        $this->middleware('auth:' . $this->getGuard(), ['only' => ['oldPassWord']]);
    }

    public function oldPassWord(Request $request)
    {
        // 限制尝试次数

        $validator = Validator::make($request->input(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(IHelper::response(
                5,
                IHelper::code_message(5) . ': ' . $validator->errors()->first()
            ));
        }

        $user = Auth::guard($this->getGuard())->user();

        $broker = $this->getBroker('oldpassword');

        if (!$broker->checkOldPassword($user, $request->input('password'))) {
            return response()->json(IHelper::response(9, IHelper::code_message(9)));
        };

        $token = Password::broker('oldpassword')->setResetToken($user);

        return response()->json(IHelper::response(0, '验证正确!', compact('token')));
    }

    public function forgot(Request $request)
    {
        $from = get_acount_type($request->input('account'));

        if ($from === 'email') {
            return $this->sendResetLinkEmail($request);
        }

        if ($from === 'tel') {
            return $this->sendResetTokenTel($request);
        }
    }

    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        return response()->json(IHelper::response(0, 'token邮件已发送, 请查收.'));
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        return response()->json(IHelper::response(10, IHelper::code_message(10)));
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $rules = $this->getResetValidationRules($request);

        $validator = Validator::make($request->input(), $rules);

        if ($validator->fails()) {
            return response()->json(IHelper::response(
                5,
                IHelper::code_message(5) . ': ' . $validator->errors()->first()
            ));
        }

        $credentials = $this->getCredentials($request);

        $broker = $this->getBroker($credentials['from']);

        $response = $broker->reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        if ($response) {
            return $this->getResetSuccessResponse($response);
        }
        return $this->getResetFailureResponse($request, $response);
    }

    protected function getCredentials(Request $request)
    {
        $parameters = ['password', 'password_confirmation', 'token', 'from'];

        if ($request->input('from') === 'email') {
            $parameters[] = 'email';
            return $request->only($parameters);
        }

        return $request->only($parameters);

    }

    protected function getBroker($from)
    {
        $broker = Password::broker($from);

        if ($from === 'oldpassword') {
            $broker->setGuard($this->getGuard());
        }
        return $broker;
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function getResetValidationRules(Request $request)
    {
        $froms = array_keys(app('config')['auth']['passwords']);

        $rules = [
            'token'    => 'required',
            'password' => 'required|confirmed|min:6',
            'from'     => 'required|in:' . implode(',', $froms),
        ];

        if ($request->input('from') === 'email') {
            # code...
        }

        return $rules;
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        return $this->reset($request);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->save();
    }

    /**
     * Get the response for after a successful password reset.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResetSuccessResponse($response)
    {
        return response()->json(IHelper::response(0, '密码修改成功'));
    }

    /**
     * Get the response for after a failing password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResetFailureResponse(Request $request, $response)
    {
        return response()->json(IHelper::response(11, IHelper::code_message(11) . $response));
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : null;
    }

}
