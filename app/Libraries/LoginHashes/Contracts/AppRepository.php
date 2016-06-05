<?php

namespace App\Libraries\LoginHashes\Contracts;

interface AppRepository
{

    /**
     * 获取Appid
     * @return int appid
     */
    public function loginHashesAppId();

    public function loginHashesMyAppId();

}
