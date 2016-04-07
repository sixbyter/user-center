<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $table = 'apps';

    public function getAppSecretByAppKey($app_key){
        return $this->select(['app_secret'])->where('app_key',$app_key)->value('app_secret');
    }

}

?>