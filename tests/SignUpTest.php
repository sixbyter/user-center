<?php

use App\Api\IHelper;
use App\User;

class SignUpTest extends TestCase
{
    protected $app_key    = 'UC';
    protected $app_secret = 'TEST1';
    protected $account    = '18825146462';
    protected $password   = 'aaaaaa';
    protected $nickname   = 'sixbyte';
    protected $headers    = [
        'Accept' => 'application/json',
    ];
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testSignOut()
    {
        $this->errorSign();
        $this->success();
        $this->errorValidate();
    }

    public function success()
    {
        $requestData = $this->getRequestData();
        $sign        = $this->makeSign($requestData);

        $requestData['sign'] = $sign;
        $this->setTestRequest($requestData)
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
                    'message' => '注册成功!',
                ],
            ]);

        $user = User::where('tel', $this->account)->first();
        $this->assertNotNull($user);
        if ($user !== null) {
            $user->delete();
        }

    }

    protected function getRequestData()
    {
        return [
            'app_key'               => $this->app_key,
            'time'                  => time(),
            'account'               => $this->account,
            'password'              => $this->password,
            'password_confirmation' => $this->password,
            'nickname'              => $this->nickname,
        ];

    }

    protected function setTestRequest($data)
    {
        return $this->post('/api1/sign/up', $data, $this->headers);
    }

    protected function errorValidate()
    {
        $requestData = $this->getRequestData();

        $data = $requestData;
        unset($data['account']);
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The account field is required.',
                ],
            ]);

        $data            = $requestData;
        $data['account'] = 'xxxxxxx';
        $sign            = $this->makeSign($data);
        $data['sign']    = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'account 请输入合法的手机号码或者邮箱',
                ],
            ]);

        $data            = $requestData;
        $data['account'] = '18825146461';
        $sign            = $this->makeSign($data);
        $data['sign']    = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'account 这个手机号码/邮箱已被注册',
                ],
            ]);

        $data             = $requestData;
        $data['password'] = 'ssss';
        $sign             = $this->makeSign($data);
        $data['sign']     = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The password must be at least 6 characters.',
                ],
            ]);

        $data = $requestData;
        unset($data['password']);
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The password field is required.',
                ],
            ]);

        $data             = $requestData;
        $data['password'] = 'sssssssss';
        $sign             = $this->makeSign($data);
        $data['sign']     = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The password confirmation does not match.',
                ],
            ]);

        $data = $requestData;
        unset($data['nickname']);
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The nickname field is required.',
                ],
            ]);

        $data             = $requestData;
        $data['nickname'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"; // 51个字符
        $sign             = $this->makeSign($data);
        $data['sign']     = $sign;
        $this->setTestRequest($data)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 5,
                    'message' => IHelper::code_message(5) . ': ' . 'The nickname may not be greater than 50 characters.',
                ],
            ]);
    }

    protected function errorSign()
    {
        $requestData = $this->getRequestData();

        // 时间过期, sign过期
        $data         = $requestData;
        $data['time'] = 123;
        $sign         = $this->makeSign($data);
        $data['sign'] = $sign;
        $this->setTestRequest($data)
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
        $this->setTestRequest($data)
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
        $this->setTestRequest($data)
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
