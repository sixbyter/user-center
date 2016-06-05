<?php

use App\Libraries\Api\IHelper;

class SignInTest extends TestCase
{
    protected $app_key    = 'UC';
    protected $app_secret = 'TEST1';
    protected $account    = '18825146461';
    protected $password   = 'aaaaaa';
    protected $headers    = [
        'Accept' => 'application/json',
    ];
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testSignIn()
    {
        $this->success();
        $this->errorSign();
        $this->errorValidate();
    }

    protected function success()
    {
        $requestData = [
            'app_key'  => $this->app_key,
            'time'     => time(),
            'account'  => $this->account,
            'password' => $this->password,
        ];
        $sign                = $this->makeSign($requestData);
        $requestData['sign'] = $sign;
        $this->post('/api1/sign/in', $requestData, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data'  => [
                    'hash',
                ],
                'error' => [
                    'code', 'message',
                ],
            ])
            ->seeJson([
                'error' => [
                    'code'    => 0,
                    'message' => '登录成功!',
                ],
            ]);
    }

    protected function errorValidate()
    {
        $requestData = [
            'app_key'  => $this->app_key,
            'time'     => time(),
            'account'  => $this->account,
            'password' => $this->password,
        ];
        // 缺少参数account
        $data = $requestData;
        unset($data['account']);
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The account field is required.',
                ],
            ]);
        // 缺少参数password
        $data = $requestData;
        unset($data['password']);
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The password field is required.',
                ],
            ]);

        // password 长度太短
        $data             = $requestData;
        $data['password'] = 'ssss';
        $sign             = $this->makeSign($data);
        $data['sign']     = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The password must be at least 6 characters.',
                ],
            ]);

        // account 格式不符合规范
        $data            = $requestData;
        $data['account'] = 'ssss';
        $sign            = $this->makeSign($data);
        $data['sign']    = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'account 请输入合法的手机号码或者邮箱',
                ],
            ]);

        // 用户不存在或密码错误
        $data            = $requestData;
        $data['account'] = 'exm@emx.com';
        $sign            = $this->makeSign($data);
        $data['sign']    = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 6,
                    'message' => IHelper::code_message(6),
                ],
            ]);
    }

    protected function errorSign()
    {
        $requestData = [
            'app_key'  => $this->app_key,
            'time'     => time(),
            'account'  => $this->account,
            'password' => $this->password,
        ];

        // 时间过期, sign过期
        $data         = $requestData;
        $data['time'] = 123;
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 2,
                    'message' => IHelper::code_message(2),
                ],
            ]);

        // sign 不正确
        $data         = $requestData;
        $data['sign'] = "xxxxxxxxxx";
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 8,
                    'message' => IHelper::code_message(8),
                ],
            ]);

        // app_key 不存在
        $data            = $requestData;
        $data['app_key'] = "xxxxxxxxxx";
        $this->post('/api1/sign/in', $data, $this->headers)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 1,
                    'message' => IHelper::code_message(1),
                ],
            ]);
    }

    protected function makeSign($arr)
    {
        ksort($arr);

        $str = http_build_query($arr);

        return sha1($str . $this->app_secret);
    }

}
