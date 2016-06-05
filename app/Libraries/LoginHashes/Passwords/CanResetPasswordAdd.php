<?php

namespace App\Libraries\LoginHashes\Passwords;

trait CanResetPasswordAdd
{

    public function getIdForPasswordReset()
    {
        return $this->id;
    }

    public function getTelForPasswordReset()
    {
        return $this->tel;
    }
}
