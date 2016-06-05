<?php

namespace App\Libraries\LoginHashes\Passwords;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new token repository instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  string  $hashKey
     * @param  int  $expires
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 60)
    {
        $this->table      = $table;
        $this->hashKey    = $hashKey;
        $this->expires    = $expires * 60;
        $this->connection = $connection;
    }

    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user, $from = 'email')
    {
        $credentials = $this->getCredentials($user, $from);
        $this->deleteExisting($user, $credentials);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($credentials, $token));

        return $token;
    }

    public function getCredentials(CanResetPasswordContract $user, $from = 'email')
    {
        if ($from === 'email') {
            $credentials = [
                'from' => $user->getEmailForPasswordReset(),
            ];
        }

        if ($from === 'oldpassword:web') {
            $credentials = [
                'from' => 'auth:web:' . $user->getIdForPasswordReset(),
            ];
        }

        if ($from === 'oldpassword:api') {
            $credentials = [
                'from' => 'auth:api:' . $user->getIdForPasswordReset(),
            ];
        }

        if ($from === 'tel') {
            $credentials = [
                'from' => $user->getTelForPasswordReset(),
            ];
        }
        return $credentials;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return int
     */
    protected function deleteExisting(CanResetPasswordContract $user, $credentials)
    {
        return $this->getTable()->where($credentials)->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $token
     * @return array
     */
    protected function getPayload($credentials, $token)
    {
        $credentials['token']      = $token;
        $credentials['created_at'] = new Carbon;
        return $credentials;
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token, $from)
    {
        $credentials = $this->getCredentials($user, $from);

        $credentials['token'] = $token;

        $token = (array) $this->getTable()->where($credentials)->first();

        return $token && !$this->tokenExpired($token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array  $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        $expirationTime = strtotime($token['created_at']) + $this->expires;

        return $expirationTime < $this->getCurrentTime();
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     */
    protected function getCurrentTime()
    {
        return time();
    }

    /**
     * Delete a token record by token.
     *
     * @param  string  $token
     * @return void
     */
    public function delete($token)
    {
        $this->getTable()->where('token', $token)->delete();
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        // return hash_hmac('sha256', Str::random(40), $this->hashKey);

        $pool   = '0123456789';
        $length = 6;

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
