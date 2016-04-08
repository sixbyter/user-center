<?php

namespace App\Http\Controllers\Api1;

use App\Api\IHelper;
use App\Http\Controllers\ApiController;
use App\User;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Validator;

class SignController extends ApiController
{

    public function __construct()
    {
        $this->middleware('auth:uc', ['only' => 'out']);
    }

    public function in(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'account'  => 'required|account',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(IHelper::response(
                5,
                IHelper::code_message(5) . ': ' . $validator->errors()->first()
            ));
        }
        $account_flag = get_acount_type($request->input('account'));

        $credentials = [
            $account_flag => $request->input('account'),
            'password'    => $request->input('password'),
        ];
        $hash = Auth::attempt($credentials);
        if ($hash) {
            return response()->json(IHelper::response(0, '登录成功!', compact('hash')));
        }

        return response()->json(IHelper::response(6, IHelper::code_message(6)));

    }

    public function out(Request $request)
    {
        Auth::logout();
        return response()->json(IHelper::response(0, '账号已退出登录.'));
    }

    public function up(Request $request)
    {
        // 数据验证

        $input     = $request->only(['account', 'password', 'password_confirmation', 'nickname']);
        $validator = Validator::make($input, [
            'account'  => 'required|account|account_unique',
            'password' => 'required|min:6|confirmed',
            'nickname' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(IHelper::response(5, IHelper::code_message(5) . ': ' . $validator->errors()->first()));
        }

        $account_flag = get_acount_type($input['account']);

        // 创建新用户.
        $new_user = [
            'password'    => bcrypt($input['password']),
            $account_flag => $input['account'],
        ];

        $user = new User();
        try {
            $user = $user->create($new_user);
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                return response()->json(IHelper::response(4, IHelper::code_message(4)));
            }
            return response()->json(IHelper::response(3, IHelper::code_message(3)));
        }

        $hash = Auth::login($user);

        return response()->json(IHelper::response(0, '注册成功!', compact('hash')));

        // 触发一个任务, 负责验证用户的邮箱或者手机号码.

    }
}
