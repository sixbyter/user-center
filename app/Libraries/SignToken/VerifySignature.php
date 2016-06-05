<?php

namespace App\Libraries\SignToken;

use App\Libraries\Api\IHelper;
use Closure;
use App\Libraries\SignToken\Contracts\AppRepository;
use Exception;

class VerifySignature
{

    protected $app_key_input = 'app_key';

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
        $debug = false;
        if ($request['debug'] && env('APP_DEBUG')) {
            $request->merge(['app_key' => 'UC']);
            $debug = true;
        }
        try {
            $appRepository = app(AppRepository::class);
            $app_secret    = $appRepository->getAppSecretByAppKey($request->input($this->app_key_input));
            if (empty($app_secret)) {
                throw new Exception(IHelper::code_message(1), 1);
            }
            if (Signature::check($request->input('sign'), $request->except('sign'), $app_secret, $debug)) {
                return $next($request);
            }
        } catch (Exception $e) {
            return response()->json(IHelper::response($e->getCode(), $e->getMessage()));
        }

        return response()->json(IHelper::response(3, IHelper::code_message(3)));
    }

}
