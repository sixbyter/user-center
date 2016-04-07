<?php

if (!function_exists('get_acount_type')) {
    function get_acount_type($account)
    {
        $account_flag = null; // tel/email
        if (is_tel($account)) {
            $account_flag = 'tel';
        } elseif (is_email($account)) {
            $account_flag = 'email';
        }
        return $account_flag;
    }
}

if (!function_exists('is_tel')) {
    function is_tel($tel)
    {
        if (preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|18[0-9]{9}$/", $tel)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_email')) {
    function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }
        return false;
    }
}
