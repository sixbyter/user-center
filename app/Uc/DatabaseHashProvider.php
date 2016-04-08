<?php

namespace App\Uc;

use Illuminate\Database\ConnectionInterface;

class DatabaseHashProvider
{
    protected $table;

    protected $conn;

    public function __construct(ConnectionInterface $conn, $table = 'login_hashes')
    {
        $this->conn  = $conn;
        $this->table = $table;

    }

    /**
     * Retrieve an item from the hash by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key, $app_id)
    {
        $record = $this->conn
            ->table($this->table)
            ->where('hash', '=', $key)
            ->where('app_id', '=', $app_id)
            ->first();
        if (empty($record)) {
            return null;
        }
        if (strtotime($record->ttl_at) < time()) {
            $this->forget($key, $app_id);
            return null;
        }
        return $record->user_id;
    }

    /**
     * Store an item in the hash for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put($key, $app_id, $value, $minutes)
    {
        // 这个值也不能无限大, 比如不能超过 2147483647, unix 的最大日期
        $ttl_at = time() + $minutes * 60;
        if ($ttl_at > 2147483647) {
            $ttl_at = 2147483647;
        }
        $login_hashe = $this->conn
            ->table($this->table)
            ->where('user_id', $value)
            ->where('app_id', '=', $app_id)
            ->first();
        if (!empty($login_hashe)) {
            return $this->conn
                ->table($this->table)
                ->where('user_id', $value)
                ->update([
                    'hash'   => $key,
                    'app_id' => $app_id,
                    'ttl_at' => date('Y-m-d H:i:s', $ttl_at),
                ]);
        }
        return $this->conn
            ->table($this->table)
            ->insert([
                'user_id' => $value,
                'hash'    => $key,
                'app_id'  => $app_id,
                'ttl_at'  => date('Y-m-d H:i:s', $ttl_at),
            ]);
    }

    /**
     * Store multiple items in the hash for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {

    }

    /**
     * Store an item in the hash indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $app_id, $value)
    {
    }

    /**
     * Remove an item from the hash.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key, $app_id)
    {
        return $this->conn
            ->table($this->table)
            ->where('hash', '=', $key)
            ->where('app_id', '=', $app_id)
            ->delete();
    }

    /**
     * Remove all items from the hash.
     *
     * @return void
     */
    public function flush()
    {

    }

    /**
     * Get the hash key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {

    }
}
