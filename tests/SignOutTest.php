<?php

use App\Api\IHelper;

class SignOutTest extends TestCase
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
    public function testSignOut()
    {
        $this->errorSign();
        $this->success();
    }

    public function success()
    {
        $requestData = $this->getRequestData();
        $sign        = $this->makeSign($requestData);

        $requestData['sign'] = $sign;
        $this->setTestRequest($requestData)
            ->seeStatusCode(200)
            ->seeJsonEquals([
                'data'  => null,
                'error' => [
                    'code'    => 0,
                    'message' => '账号已退出登录.',
                ],
            ]);
    }

    protected function getRequestData()
    {
        $hash = $this->getHash();

        return [
            'app_key' => $this->app_key,
            'time'    => time(),
            'hash'    => $hash,
        ];

    }

    protected function getHash()
    {
        $requestData = [
            'app_key'  => $this->app_key,
            'time'     => time(),
            'account'  => $this->account,
            'password' => $this->password,
        ];
        $sign                = $this->makeSign($requestData);
        $requestData['sign'] = $sign;
        $this->post('/api1/sign/in', $requestData, $this->headers);

        $content  = $this->response->getContent();
        $response = json_decode($content, true);
        if (isset($response['data']['hash'])) {
            return $response['data']['hash'];
        } else {
            throw new Exception("登录失败, 获取不到hash: " . $content, 1);

        }
    }

    protected function setTestRequest($data)
    {
        return $this->get('/api1/sign/out?' . http_build_query($data), $this->headers);
    }

    protected function errorValidate()
    {

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
