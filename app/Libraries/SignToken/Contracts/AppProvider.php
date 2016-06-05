<?php

namespace App\Libraries\SignToken\Contracts;

interface AppProvider
{

    public function getSecretByKey($key);

    public function checkSecretByKey($key);

}
