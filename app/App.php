<?php

namespace App;

use App\Libraries\LoginHashes\Contracts\AppProvider as LoginHashesAppProvider;
use App\Libraries\SignToken\Contracts\AppProvider as SignTokenAppProvider;
use Illuminate\Database\Eloquent\Model;

class App extends Model implements LoginHashesAppProvider, SignTokenAppProvider
{
    protected $table = 'apps';

    public function getAppSecretByAppKey($app_key)
    {
        return $this->select(['app_secret'])->where('app_key', $app_key)->value('app_secret');
    }

    public function getAppIdByCredentials($credentials)
    {
        $keys  = ['id', 'app_key'];
        $where = [];
        foreach ($keys as $key) {
            if (isset($credentials[$key])) {
                $where[$key] = $credentials[$key];
            }
        }

        $app = $this->select('id')->where($where)->first();
        if ($app === null) {
            return 0;
        }

        return $app['id'];

    }

    public function getSecretByKey($key)
    {
        $app = $this->select('app_secret')->where(['app_key' => $key])->first();

        if ($app === null) {
            return 0;
        }

        return $app['app_secret'];
    }

    public function checkSecretByKey($key)
    {
        $count = $this->where(['app_key' => $key])->count();

        if ($count > 0) {
            return true;
        }
        return false;
    }

}
