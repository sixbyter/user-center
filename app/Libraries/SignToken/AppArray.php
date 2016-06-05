<?php

namespace App\Libraries\SignToken;

use App\Libraries\SignToken\Contracts\AppProvider;
use ArrayAccess;

class AppArray implements AppProvider
{
    // 这个可以来自配置文件
    protected $apps = [

        'app_key' => 'app_secret',
        'UC'      => 'TEST1',
        'WEB'     => 'WEB1',

    ];

    public function getSecretByKey($key)
    {
        $apps = $this->getApps();

        return isset($apps[$key]) ? $apps[$key] : null;
    }

    public function checkSecretByKey($key)
    {
        $apps = $this->getApps();

        return isset($apps[$key]);
    }

    public function getApps()
    {
        return $this->apps;
    }

    public function setApps($apps)
    {
        if (is_array($apps) || $apps instanceof ArrayAccess) {
            $this->apps = $apps;
        }
    }

}
