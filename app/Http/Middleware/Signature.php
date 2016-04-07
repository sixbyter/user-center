<?php

namespace App\Http\Middleware;

use App\Api\IHelper;
use App\Api\Signature;
use Closure;
use Exception;
use App\User;
use App\App as AppModel;


class Signature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $app = $this->getApp($request);
            if (Signature::check($request->input('sign'), $request->except('sign'),$app->app_secret)) {
                $this->setApp($request, $app);
                return $next($request);
            }
        } catch (Exception $e) {
            return response()->json(IHelper::response($e->getCode(), $e->getMessage()));
        }

        return response()->json(IHelper::response(3, IHelper::code_message(3)));
    }

    protected function setApp($request,$app){
        $request->authApp = $app;
    }

    protected function getApp($request){
        $app_key = $request->input('app_key');
        if (empty($app_key)) {
            throw new Exception(IHelper::code_message(1), 1);
        }

        $app = AppModel::where('app_key',$app_key)->first();
        if ($app === null) {
            throw new Exception(IHelper::code_message(1), 1);
        }

        return $app;
    }
}
