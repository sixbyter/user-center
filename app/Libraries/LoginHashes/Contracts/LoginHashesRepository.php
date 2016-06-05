<?php

namespace App\Libraries\LoginHashes\Contracts;

interface LoginHashesRepository
{
    /**
     * Retrieve an item from the hash by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key, $app_id);

    /**
     * Store an item in the hash for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put($key, $app_id, $value, $minutes);

    /**
     * Store multiple items in the hash for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $minutes
     * @return void
     */
    public function putMany(array $values, $minutes);

    /**
     * Store an item in the hash indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $app_id, $value);

    /**
     * Remove an item from the hash.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key, $app_id);

    /**
     * Remove all items from the hash.
     *
     * @return void
     */
    public function flush();

    /**
     * Get the hash key prefix.
     *
     * @return string
     */
    public function getPrefix();
}
