<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

// Route::get('/', function () {
//     return view('welcome');
// });

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
 */

Route::group(['middleware' => ['api'], 'prefix' => 'api1'], function () {

    Route::get('/sign/out', [
        'as'   => 'api1.sigin.out',
        'uses' => 'Api1\SignController@out',
    ]);

    Route::post('/sign/up', [
        'as'   => 'api1.sigin.up',
        'uses' => 'Api1\SignController@up',
    ]);

    Route::post('/sign/in', [
        'as'   => 'api1.sigin.in',
        'uses' => 'Api1\SignController@in',
    ]);

    Route::post('/password/oldpassword', [
        'as'   => 'api1.password.oldpassword',
        'uses' => 'Api1\PasswordController@oldPassWord',
    ]);

    Route::post('/password/forgot', [
        'as'   => 'api1.password.forgot',
        'uses' => 'Api1\PasswordController@forgot',
    ]);

    Route::post('/password/reset', [
        'as'   => 'api1.password.reset',
        'uses' => 'Api1\PasswordController@reset',
    ]);

});

Route::group(['middleware' => 'web'], function () {

    Route::get('login', [
        'as'   => 'web.login.form',
        'uses' => 'Web\AuthController@showLoginForm',
    ]);

    Route::post('login', [
        'as'   => 'web.login.post',
        'uses' => 'Web\AuthController@login',
    ]);
    Route::get('logout', 'Web\AuthController@logout');

    Route::get('register', [
        'as'   => 'web.register.form',
        'uses' => 'Web\AuthController@showRegistrationForm',
    ]);
    Route::post('register', [
        'as'   => 'web.register.post',
        'uses' => 'Web\AuthController@register',
    ]);

    Route::get('password/reset/{from}', [
        'as'   => 'web.reset.form',
        'uses' => 'Web\PasswordController@showResetForm',
    ]);

    Route::post('password/reset', [
        'as'   => 'web.reset.post',
        'uses' => 'Web\PasswordController@reset',
    ]);

    Route::get('password/forgot', [
        'as'   => 'web.forgot.form',
        'uses' => 'Web\PasswordController@showForgotForm',
    ]);

    Route::post('password/forgot', [
        'as'   => 'web.forgot.post',
        'uses' => 'Web\PasswordController@forgot',
    ]);

    Route::get('/password/verifyoldpassword', [
        'as'   => 'web.verifyoldpassword.form',
        'uses' => 'Web\PasswordController@getVerifyOldPassWord',
    ]);
    Route::post('/password/verifyoldpassword', [
        'as'   => 'web.verifyoldpassword.post',
        'uses' => 'Web\PasswordController@postVerifyOldPassWord',
    ]);

    Route::get('/home', 'Web\HomeController@index');
    Route::get('/', 'Web\HomeController@index');
});
