<?php

namespace App\Libraries\LoginHashes\Passwords;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Auth;
use Closure;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Arr;
use UnexpectedValueException;

class OldPasswordBroker implements PasswordBrokerContract
{
    /**
     * The password token repository.
     *
     * @var \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * The mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * The view of the password reset link e-mail.
     *
     * @var string
     */
    protected $emailView;

    /**
     * The custom password validator callback.
     *
     * @var \Closure
     */
    protected $passwordValidator;

    protected $hasher;

    protected $guard = null;

    /**
     * Create a new password broker instance.
     *
     * @param  \Illuminate\Auth\Passwords\TokenRepositoryInterface  $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  string  $emailView
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens,
        UserProvider $users, HasherContract $hasher) {
        $this->users  = $users;
        $this->tokens = $tokens;
        $this->hasher = $hasher;
    }

    public function setGuard($guard)
    {
        $this->guard = $guard;
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function checkOldPassword(CanResetPasswordContract $user, $password)
    {

        if ($this->hasher->check($password, $user->getAuthPassword())) {
            return true;
        }
        return false;
    }

    public function setResetToken(CanResetPasswordContract $user)
    {
        $token = $this->tokens->create($user, 'oldpassword:' . $this->getGuard());
        return $token;
    }

    /**
     * Reset the password for the given token.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $user = $this->validateReset($credentials);

        if (!$user instanceof CanResetPasswordContract) {
            return $user;
        }

        $pass = $credentials['password'];

        // Once we have called this callback, we will remove this token row from the
        // table and return the response from this callback so the user gets sent
        // to the destination given by the developers from the callback return.
        call_user_func($callback, $user, $pass);

        $this->tokens->delete($credentials['token']);

        return PasswordBrokerContract::PASSWORD_RESET;
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword
     */
    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return PasswordBrokerContract::INVALID_USER;
        }

        if (!$this->validateNewPassword($credentials)) {
            return PasswordBrokerContract::INVALID_PASSWORD;
        }

        if (!$this->tokens->exists($user, $credentials['token'], 'oldpassword:' . $this->getGuard())) {
            return PasswordBrokerContract::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Set a custom password validator.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function validator(Closure $callback)
    {
        $this->passwordValidator = $callback;
    }

    /**
     * Determine if the passwords match for the request.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validateNewPassword(array $credentials)
    {
        list($password, $confirm) = [
            $credentials['password'],
            $credentials['password_confirmation'],
        ];

        if (isset($this->passwordValidator)) {
            return call_user_func(
                $this->passwordValidator, $credentials) && $password === $confirm;
        }

        return $this->validatePasswordWithDefaults($credentials);
    }

    /**
     * Determine if the passwords are valid for the request.
     *
     * @param  array  $credentials
     * @return bool
     */
    protected function validatePasswordWithDefaults(array $credentials)
    {
        list($password, $confirm) = [
            $credentials['password'],
            $credentials['password_confirmation'],
        ];

        return $password === $confirm && mb_strlen($password) >= 6;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $user = Auth::guard($this->getGuard())->user();

        if ($user && !$user instanceof CanResetPasswordContract) {
            throw new UnexpectedValueException('User must implement CanResetPassword interface.');
        }

        return $user;
    }

    /**
     * Get the password reset token repository implementation.
     *
     * @return \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}
