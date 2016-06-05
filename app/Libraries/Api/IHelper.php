<?php
namespace App\Libraries\Api;

class IHelper
{

    public static function response($code = 0, $message = '成功的响应', $data = null)
    {
        return [
            'data'  => $data,
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ];

    }

    public static function code_message($code)
    {

        return [
            1  => 'app_key 不合法',
            2  => '服务器的时间和客户端的时间不对应...',
            3  => '未知错误',
            4  => '该唯一标识已存在',
            5  => '数据验证失败',
            6  => '登录失败, 用户不存在或者账号密码错误.',
            7  => '登录超时, 请重新登录',
            8  => 'sign 不正确',
            9  => '旧密码错误',
            10 => '非法用户',
            11 => '密码修改失败',
        ][$code];
    }

}
