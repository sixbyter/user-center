<?php

namespace App\Libraries\SignToken\Contracts;

interface AppRepository
{

    /**
     * 获取Appid
     * @return int appid
     */
    public function getAppSecretByAppKey($app_key);

    public function checkAppExistByAppKey($app_key);

}
