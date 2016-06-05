<?php

namespace App\Libraries\LoginHashes\Contracts;

interface AppProvider
{

    /**
     * 获取Appid
     * @return int appid
     */
    public function getAppIdByCredentials($credentials);

}
