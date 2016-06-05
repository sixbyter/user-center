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
    // Authentication Routes...
    Route::get('login', 'Auth\AuthController@showLoginForm');
    Route::post('login', 'Auth\AuthController@login');
    Route::get('logout', 'Auth\AuthController@logout');

    // Registration Routes...
    Route::get('register', 'Auth\AuthController@showRegistrationForm');
    Route::post('register', 'Auth\AuthController@register');

    Route::post('password/reset', 'Auth\PasswordController@reset');

    Route::get('password/reset/{from}', 'Auth\PasswordController@showResetForm');

    Route::get('password/forgot', 'Auth\PasswordController@showForgotForm');

    Route::post('password/forgot', 'Auth\PasswordController@forgot');

    Route::get('/password/verifyoldpassword', 'Auth\PasswordController@getVerifyOldPassWord');
    Route::post('/password/verifyoldpassword', 'Auth\PasswordController@postVerifyOldPassWord');

    Route::get('/home', 'HomeController@index');
    Route::get('/', 'HomeController@index');
});
