<?php

namespace App\Libraries\LoginHashes;

use App\Libraries\LoginHashes\Contracts\AppProvider;
use App\Libraries\LoginHashes\Contracts\AppRepository as AppRepositoryInterface;
use Exception;
use Illuminate\Http\Request;

class AppRepository implements AppRepositoryInterface
{

    protected $app_key_input = 'app_key';

    protected $my_app_id = 0;

    public function __construct(Request $request, AppProvider $provider)
    {
        $this->request  = $request;
        $this->provider = $provider;
    }

    public function loginHashesAppId()
    {
        $credentials = [
            $this->app_key_input => $this->request->input($this->app_key_input),
        ];
        $id = $this->provider->getAppIdByCredentials($credentials);

        if (!empty($id) && is_integer($id) && $id > 0) {
            return $id;
        }
        return 0;
    }

    public function loginHashesMyAppId()
    {
        return $this->my_app_id;
    }

    public function setMyAppId($id)
    {
        if (empty($id) || !is_integer($id) || $id <= 0) {
            throw new Exception("invial id");

        }
        $this->my_app_id = $id;
    }

    public function setAppKeyInput($str)
    {
        $this->app_key_input = $str;
    }

    public function getAppKeyInput($str)
    {
        return $this->app_key_input;
    }

}
