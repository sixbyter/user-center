<?php

namespace App\Uc;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Log;

class HashGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The name of the field on the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request, DatabaseHashProvider $hash_provider)
    {
        $this->request         = $request;
        $this->provider        = $provider;
        $this->hash_provider = $hash_provider;
        $this->inputKey        = 'hash';
        $this->storageKey      = 'hash';
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $user_id = $this->getIdForHash();

        if (!empty($user_id)) {
            $user = $this->provider->retrieveById($user_id);
        }

        return $this->user = $user;
    }

    public function getIdForHash()
    {
        $token  = $this->getHashForRequest();
        $app_id = $this->getAppId();
        if (!empty($token)) {
            return $this->hash_provider->get($token, $app_id);
        }
        return null;
    }

    public function login(AuthenticatableContract $user)
    {
        $hash   = $this->getHashUniqueKey();
        $app_id = $this->getAppId();
        $this->hash_provider->put(
            $hash,
            $app_id,
            $user->getAuthIdentifier(),
            $this->getRememberForRequest()
        );

        $this->setUser($user);

        return $hash;
    }

    public function logout()
    {

        $user = $this->user();

        if ($user !== null) {
            $thi->clearUserHash();
        }

        $this->user = null;
    }

    public function clearUserHash()
    {
        $token  = $this->getHashForRequest();
        $app_id = $this->getAppId();
        $this->hash_provider->forget($token, $app_id);
    }

    public function setUser(AuthenticatableContract $user)
    {
        $this->user = $user;
    }

    protected function getHashUniqueKey()
    {
        return str_random(100);
    }

    protected function getRememberForRequest()
    {
        $remember = $this->request->input('remember');
        return 30;
    }

    protected function getHashForRequest()
    {
        return $this->request->input($this->inputKey);
    }

    protected function getAppId()
    {
        return $this->request->authApp->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (is_null($user)) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);

    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @param  bool   $login
     * @return bool
     */
    public function attempt(array $credentials = [])
    {
        // $this->fireAttemptEvent($credentials, $remember, $login);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if (is_null($user)) {
            return false;
        }

        if ($this->provider->validateCredentials($user, $credentials)) {
            return $this->login($user);
        }

        return false;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
