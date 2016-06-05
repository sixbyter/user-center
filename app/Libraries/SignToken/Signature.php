<?php
namespace App\Libraries\SignToken;

use App\Libraries\Api\IHelper;
use Exception;

class Signature
{

    public static function check($sign, $arr, $app_secret, $debug = false)
    {
        if ($debug === true) {
            return true;
        }
        // 验证 app_key 是否合法
        if (!isset($arr['app_key'])) {
            throw new Exception(IHelper::code_message(1), 1);
        }

        // 验证时间, 对比服务器和客户端的时间. 不超过 30 分钟
        if ((time() - (int) $arr['time']) > 60 * 30) {
            throw new Exception(IHelper::code_message(2), 2);
        }
        // 验证 token
        if (static::make($arr, $app_secret) === $sign) {
            return true;
        }

        throw new Exception(IHelper::code_message(8), 8);

    }

    public static function make($arr, $app_secret)
    {
        // 参数排序
        ksort($arr);

        $str = http_build_query($arr);

        return sha1($str . $app_secret);

    }

}
