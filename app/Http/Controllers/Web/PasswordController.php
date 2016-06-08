<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\WebController;
use App\Libraries\LoginHashes\Passwords\EmailForgot;
use App\Libraries\LoginHashes\Passwords\TelForgot;
use Auth;
use Illuminate\Http\Request;
use Password;

class PasswordController extends WebController
{

    use EmailForgot, TelForgot;

    protected $guard = 'web';

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
        $this->middleware('auth:' . $this->getGuard(), ['only' => ['getVerifyOldPassWord', 'postVerifyOldPassWord']]);
    }

    public function getVerifyOldPassWord(Request $request)
    {
        return view('auth.passwords.verifyoldpassword');
    }

    public function postVerifyOldPassWord(Request $request)
    {
        // 限制尝试次数
        $this->validate($request, [
            'password' => 'required|min:6',
        ]);

        $user = Auth::guard($this->getGuard())->user();

        $broker = $this->getBroker('oldpassword');

        if (!$broker->checkOldPassword($user, $request->input('password'))) {
            return redirect()->back()->withErrors(['password' => 'password wrong']);
        };

        $token = Password::broker('oldpassword')->setResetToken($user);

        return redirect('password/reset/' . 'oldpassword/' . $token);
    }

    public function showResetForm(Request $request, $from)
    {
        $froms = array_keys(app('config')['auth']['passwords']);

        if (!in_array($from, $froms)) {
            return 'inval from';
        }

        $broker = $this->getBroker($from);

        $credentials = $request->only('token', 'email', 'tel');

        $user = $broker->getUser($credentials);

        $token = $credentials['token'];

        if (!$user) {
            return 'inval token';
        }

        return view('auth.passwords.reset')->with(compact('token', 'from', 'user'));
    }

    public function showForgotForm(Request $request)
    {
        if (property_exists($this, 'linkRequestView')) {
            return view($this->linkRequestView);
        }

        if (view()->exists('auth.passwords.forgot')) {
            return view('auth.passwords.forgot');
        }

        return view('auth.password');
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

    public function redirectPath()
    {
        Auth::guard($this->getGuard())->logout();

        return '/login';
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {

        $this->validate($request, $this->getResetValidationRules());

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
    protected function getResetValidationRules()
    {
        $froms = array_keys(app('config')['auth']['passwords']);

        $rules = [
            'token'    => 'required',
            'password' => 'required|confirmed|min:6',
            'from'     => 'required|in:' . implode(',', $froms),
        ];

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
        return redirect($this->redirectPath())->with('status', trans($response));
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
        return redirect()->back()
            ->withErrors(['status' => trans($response)]);
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

    /**
     * Get the response for after the reset link has been successfully sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        return redirect()->back()->with('status', trans($response));
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        return redirect()->back()->withErrors(['account' => trans($response)]);
    }

}
