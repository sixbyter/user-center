<?php

namespace App\Libraries\SignToken;

use App\Libraries\SignToken\Contracts\AppProvider;
use App\Libraries\SignToken\Contracts\AppRepository as AppRepositoryInterface;

class AppRepository implements AppRepositoryInterface
{

    protected $my_app_id = 0;

    public function __construct(AppProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getAppSecretByAppKey($app_key)
    {
        return $this->provider->getSecretByKey($app_key);
    }

    public function checkAppExistByAppKey($app_key)
    {
        return $this->provider->checkSecretByKey($app_key);
    }

}
